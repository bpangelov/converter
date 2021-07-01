import boto3
import uuid
import time

def create_bucket(bucket_name):
    print("Creating new S3 bucket...")

    s3_client = boto3.client('s3')
    bucket = s3_client.create_bucket(Bucket=bucket_name)
    return bucket


def create_rds_database(db_name, master_username, master_password):
    print("Creating new RDS database")

    rds_sg = create_rds_security_group(db_name + '-sg')

    rds_client = boto3.client('rds')
    return rds_client.create_db_instance(
        DBInstanceIdentifier=db_name,
        AllocatedStorage=10,
        DBInstanceClass='db.t2.micro',
        Engine='mysql',
        MasterUsername=master_username,
        MasterUserPassword=master_password,
        BackupRetentionPeriod=0,
        MultiAZ=False,
        PubliclyAccessible=True,
        VpcSecurityGroupIds = [rds_sg],
        Tags=[
            {
                'Key': 'Deployment',
                'Value': 'automatic'
            },
        ],
    )


def await_rds_host(db_name):
    print("Awaiting RDS database creation. This may take a while...")

    rds_client = boto3.client('rds')
    while True:
        resp = rds_client.describe_db_instances(DBInstanceIdentifier=db_name)
        if 'DBInstances' in resp and len(resp['DBInstances']) > 0 and 'Endpoint' in resp['DBInstances'][0]:
            endpoint = resp['DBInstances'][0]['Endpoint']
            return str(endpoint['Address']) + ":" + str(endpoint['Port'])

        time.sleep(5)


def create_rds_security_group(sg_name):
    ec2 = boto3.client('ec2')
    
    response = ec2.create_security_group(GroupName=sg_name, 
                                        Description='Security group for RDS databases')
    security_group_id = response['GroupId']

    ec2.authorize_security_group_ingress(
        GroupId=security_group_id,
        IpPermissions=[
            {'IpProtocol': 'tcp',
             'FromPort': 3306,
             'ToPort': 3306,
             'IpRanges': [{'CidrIp': '0.0.0.0/0'}]},
        ])
    return security_group_id


def create_ec2_security_group(sg_name):
    ec2 = boto3.client('ec2')
    
    response = ec2.create_security_group(GroupName=sg_name, 
                                        Description='Security group for EC2 instances')
    security_group_id = response['GroupId']

    ec2.authorize_security_group_ingress(
        GroupId=security_group_id,
        IpPermissions=[
            {'IpProtocol': 'tcp',
             'FromPort': 80,
             'ToPort': 80,
             'IpRanges': [{'CidrIp': '0.0.0.0/0'}]},
            {'IpProtocol': 'tcp',
             'FromPort': 22,
             'ToPort': 22,
             'IpRanges': [{'CidrIp': '0.0.0.0/0'}]}
        ])
    return security_group_id


def create_s3_instance_profile(deployment_id):
    print("Creating IAM instance profile...")   

    role_name = "role-" + deployment_id
    policy_document = """{
        "Version": "2012-10-17",
        "Statement": [
            {
            "Effect": "Allow",
            "Principal": {
                "Service": "ec2.amazonaws.com"
            },
            "Action": "sts:AssumeRole"
            }
        ]
    }"""

    iam_client = boto3.client('iam')
    iam_client.create_role(
        RoleName=role_name,
        AssumeRolePolicyDocument=policy_document,
        Tags=[
            {
                'Key': 'Deployment',
                'Value': 'automatic'
            },
        ]
    )

    iam_client.attach_role_policy(
        RoleName=role_name,
        PolicyArn='arn:aws:iam::aws:policy/AmazonS3FullAccess'
    )

    instance_profile_name = "ipn-" + deployment_id
    instance_profile = iam_client.create_instance_profile(InstanceProfileName=instance_profile_name)

    iam_client.add_role_to_instance_profile(
        InstanceProfileName=instance_profile_name,
        RoleName=role_name,
    )

    return instance_profile['InstanceProfile']['Arn']


def create_ec2_instance(deployment_id, s3_bucket, db_host, db_user, db_password):
    print("Creating EC2 instance...")

    ec2 = boto3.resource('ec2')

    key_name = 'ec2-keypair-' + deployment_id + '.pem'
    # create a file to store the key locally
    with open(key_name, 'w') as key_file:
        # call the boto3 ec2 function to create a key pair
        key_pair = ec2.create_key_pair(KeyName=key_name)

        # capture the key and store it in a file
        key_content = str(key_pair.key_material)
        key_file.write(key_content)

    with open("../user-data", "r") as user_data_file:
        user_data = user_data_file.read()

    # hardcoded for now
    user_data = user_data.replace('$S3_BUCKET = "<name_of_bucket>"', '$S3_BUCKET = "' + s3_bucket + '"')
    user_data = user_data.replace('$DB_USER = "admin"', '$DB_USER = "' + db_user + '"')
    user_data = user_data.replace('$DB_PASS = "password"', '$DB_PASS = "' + db_password + '"')
    user_data = user_data.replace('$DB_HOST = "<rds_host>"', '$DB_HOST = "' + db_host + '"')

    ec2_sg = create_ec2_security_group('sec-gr-ec2-' + deployment_id)

    instance_profile_arn = create_s3_instance_profile(deployment_id)

    time.sleep(10)

    ec2 = ec2.create_instances(
        ImageId='ami-0aeeebd8d2ab47354',
        MinCount=1,
        MaxCount=1,
        InstanceType='t2.micro',
        KeyName=key_name,
        SecurityGroupIds = [ec2_sg],
        IamInstanceProfile={
            'Arn': instance_profile_arn,
        },
        UserData=user_data,
    )
    return ec2[0].id

def await_ec2_instance(instance_id):
    print("Awaiting EC2 creation. This may take a while...")

    ec2_client = boto3.client('ec2')
    while True:
        resp = ec2_client.describe_instances(InstanceIds=[instance_id])
        if 'Reservations' not in resp or len(resp['Reservations']) == 0 or 'Instances' not in resp['Reservations'][0]:
            continue

        instances = resp['Reservations'][0]['Instances']
        if len(instances) > 0 and 'PublicIpAddress' in instances[0]:
            return instances[0]['PublicIpAddress']
        
        time.sleep(5)

def deploy():
    print("Starting deployment...")
    deployment_id = str(uuid.uuid4())

    while True:
        s3 = input("Do you want to create a new(N) S3 bucket or use an existing one(E)? ")
        if s3 == 'N':
            s3_bucket = create_bucket("converter-s3-" + deployment_id)['Location'][1:]
            print("Bucket " + s3_bucket + " created")
            break
        elif s3 == 'E':
            s3_bucket = input("Bucket name: ")
            break

    while True:
        rds = input("Do you want to create a new(N) RDS database or use an existing one(E)? ")
        if rds == 'N':
            db_username = input("Master username: ")
            db_password = input("Master password: ")

            db_name = 'converter-db-' + deployment_id
            create_rds_database(db_name, db_username, db_password)
            db_host = await_rds_host(db_name)
            print("Database " + db_name + " created with host: " + db_host)
            break
        elif rds == 'E':
            db_host = input("Database host: ")
            db_username = input("Master username: ")
            db_password = input("Master password: ")
            break
        
    ec2_id = create_ec2_instance(deployment_id, s3_bucket, db_host, db_username, db_password)
    public_ip = await_ec2_instance(ec2_id)

    convertor_ip = "http://" + public_ip + "/converter.php"
    print("Convertor is up and running on: " + convertor_ip)
    

if __name__ == "__main__":
    deploy()
