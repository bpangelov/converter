import boto3
import uuid

def create_bucket(bucket_name, region=None):
    s3_client = boto3.client('s3')
    bucket = s3_client.create_bucket(Bucket=bucket_name)
    return bucket


def create_rds_database(db_engine, master_username, master_password):
    rds_client = boto3.client('rds')
    rds_client.create_db_instance(
        DBInstanceIdentifier='converter-prod-db-auto',
        AllocatedStorage=10,
        DBInstanceClass='db.t2.micro',
        Engine=db_engine,
        MasterUsername=master_username,
        MasterUserPassword=master_password,
        BackupRetentionPeriod=0,
        MultiAZ=False,
        PubliclyAccessible=True,
        Tags=[
            {
                'Key': 'Deployment',
                'Value': 'automatic'
            },
        ],
    )
    

def createa_iam_role():
    permissions = """
        {
            "Version": "2012-10-17",
            "Statement": [
                {
                    "Effect": "Allow",
                    "Action": "s3:*",
                    "Resource": "*"
                }
            ]
        }"""

    iam_client = boto3.client('iam')
    iam_client.create_role(
        RoleName='converter-prod-s3-role-auto',
        AssumeRolePolicyDocument=permissions,
        Tags=[
            {
                'Key': 'Deployment',
                'Value': 'automatic'
            },
        ]
    )


def create_security_group(id):
    ec2 = boto3.client('ec2')
    
    response = ec2.create_security_group(GroupName='security-group-' + id, 
                                        Description='Security group for automatically deployed ec2 instances')
    security_group_id = response['GroupId']

    data = ec2.authorize_security_group_ingress(
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
    print('Ingress Successfully Set %s' % data)


def create_ec2_instance(id):
    ec2 = boto3.resource('ec2')

    key_name = 'ec2-keypair-' + id + '.pem'
    # create a file to store the key locally
    with open(key_name, 'w') as key_file:
        # call the boto ec2 function to create a key pair
        key_pair = ec2.create_key_pair(KeyName=key_name)

        # capture the key and store it in a file
        KeyPairOut = str(key_pair.key_material)
        key_file.write(KeyPairOut)

    with open("../user-data", "r") as user_data_file:
        user_data = user_data_file.read()

    ec2.create_instances(
        ImageId='ami-0aeeebd8d2ab47354',
        MinCount=1,
        MaxCount=1,
        InstanceType='t2.micro',
        KeyName=key_name,
        UserData=user_data
    )


if __name__ == "__main__":
    id = str(uuid.uuid4())
    create_security_group(id)
