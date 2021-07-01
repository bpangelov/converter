## AWS Deployment

This python script allows for an easy and straighforward AWS deployment of the converter
web application. These are the AWS resources which are necessary to run the app on AWS:

* S3 Bucket - The app requires a S3 bucket to store all saved transformations. The script
allows you to choose whether to create a new bucket or use an existing one.

* RDS Database - The app needs a relational database to store information such as user
info, configuration info, etc. The supported database engines are MySQL and Aurora. Again,
you can choose whether to create a new or use an existing RDS database. Bear in mind that
if you choose to create a new instance, some time will be needed for it to start.

* EC2 Instance - After all, the converter app is deployed in a docker container on an 
EC2 instance. It uses the already created S3 Bucket and RDS database. In order to have permissions to use the S3 bucket, it assumes a newly created IAM role.
