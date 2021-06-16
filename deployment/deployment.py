import logging
import boto3
import json
from botocore.exceptions import ClientError

def create_bucket(bucket_name, region=None):
    """Create an S3 bucket in a specified region

    If a region is not specified, the bucket is created in the S3 default
    region (us-east-1).

    :param bucket_name: Bucket to create
    :param region: String region to create bucket in, e.g., 'us-west-2'
    :return: True if bucket created, else False
    """

    try:
        if region is None:
            s3_client = boto3.client('s3')
            s3_client.create_bucket(Bucket=bucket_name)
        else:
            s3_client = boto3.client('s3', region_name=region)
            location = {'LocationConstraint': region}
            s3_client.create_bucket(Bucket=bucket_name,
                                    CreateBucketConfiguration=location)
    except ClientError as e:
        logging.error(e)
        return False
    return True


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


if __name__ == "__main__":
    createa_iam_role()
