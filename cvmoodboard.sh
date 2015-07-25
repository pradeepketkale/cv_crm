#!/bin/bash



rsync -avz --port=22  /var/www/html/moodboard/ root@175.41.141.42:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.144.20.107:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.134.121.60:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.134.121.238:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.144.87.5:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.144.88.10:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.144.80.17:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.144.14.224:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.139.58.82:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.139.47.112:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.138.137.43:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.144.93.209:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.139.53.95:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.135.37.51:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"
rsync -avz --port=22  /var/www/html/moodboard/ root@10.138.150.147:/var/www/html/moodboard/ -e "ssh -i /home/newserver1.pem"


echo 'rsync done'

