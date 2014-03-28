#!/bin/python
import RPi.GPIO as GPIO
import time
from subprocess import call

GPIO_PIR = 25
GPIO_FLASH = 23

GPIO.setmode(GPIO.BCM)
print "PIR Module Test (CTRL-C to exit)"

GPIO.setup(GPIO_PIR,GPIO.IN)
GPIO.setup(GPIO_FLASH,GPIO.OUT)
GPIO.output(GPIO_FLASH, False)

def reset_gpio():
    print "exiting"
    GPIO.cleanup()

import atexit
atexit.register(reset_gpio)

def take_picture():
    print "Taking Picture"
    time_str = time.strftime("%a_%eD_%Hh_%Mm_%Ss").lower()
    file_name = "/var/www/images/pir_image_" + time_str + "_%04d.jpg" 
    GPIO.output(GPIO_FLASH, True)
    call(["raspistill",
          "--ev", "+10",
          "--timeout", "10000",
          "--timelapse", "1000",
          "--quality", "75",
          "--height", "972",
          "--width", "1296",
          "--rotation", "180",
          "--ISO", "800",
          "--exposure", "night",
          "--metering", "spot",
          "-o", file_name])
    GPIO.output(GPIO_FLASH, False)

old_state = 0
while True:
    current_state = GPIO.input(GPIO_PIR)
    if current_state == 1:
        print "PIR is active"
        if old_state == 0:
            take_picture()
            old_state = 1
    else:
        old_state = 0
    time.sleep(1)

