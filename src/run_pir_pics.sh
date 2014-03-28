#!/bin/bash

python /home/pi/pir_pics.py >> /var/www/images/output.txt 2>&1 & echo $! > /var/www/images/pidfile.txt