#!/bin/bash

scp -i /home/newserver1.pem /var/www/html/rssxmlfiles/cv-product-1.xml ec2-user@10.128.90.228:/var/www/html/xmldata/
scp -i /home/newserver1.pem /var/www/html/rssxmlfiles/cv-product-2.xml ec2-user@10.128.90.228:/var/www/html/xmldata/
scp -i /home/newserver1.pem /var/www/html/rssxmlfiles/cv-product-3.xml ec2-user@10.128.90.228:/var/www/html/xmldata/
scp -i /home/newserver1.pem /var/www/html/rssxmlfiles/cv-product-4.xml ec2-user@10.128.90.228:/var/www/html/xmldata/
