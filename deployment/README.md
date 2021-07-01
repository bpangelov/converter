## AWS Deployment

This python script allows for an easy and straighforward AWS deployment of the converter
web application. These are the AWS resources which are necessary to run the app on AWS:

* S3 Bucket - The app requires a S3 bucket to store all saved transformations. The script
lets you to choose whether to create a new bucket or use an existing one.

* RDS Database - The app needs a relational database to store information such as user
info, configuration info, etc. The supported database engines are MySQL and Aurora. Again,
you can choose whether to create a new or use an existing RDS database. Bear in mind that
if you choose to create a new instance, some time will be needed for it to start.

* EC2 Instance - After all, the converter app is deployed in a docker container on an 
EC2 instance. It uses the already created S3 Bucket and RDS database. In order to have 
permissions to use the S3 bucket, the EC2 instance assumes a newly created IAM role.

Two security groups are also created to customize the inbound traffic to the RDS and EC2 
instances.

### How to run
1. Install `boto3` SDK via pip

```bash
pip3 install boto3
```

2. Configure AWS credentials via AWS config file([instructions](https://boto3.amazonaws.com/v1/documentation/api/latest/guide/credentials.html#aws-config-file)).

3. Navigate to the `converter/deployment` directory and run

```bash
python3 deployment.py
```

