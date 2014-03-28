#!/bin/python
from subprocess import call
import glob
import re
import datetime
import os.path

IMAGE_DIR = "/var/www/images/"

for f in glob.glob(IMAGE_DIR + '*.jpg'):
  print "Processing %s" % (f,)
  found = re.search(r'_thumb',f)
  if found != None:
      print "Skipping thumbnail"
  else:
      print "Full size image %s" % (f,)
      output_file = f[:-4] + "_thumb.jpg"
      print "File: %s" % (output_file,)
      if os.path.isfile(output_file):
        print "Thumbnail already exists"
      else:
        print "Creating thumbnail %s for image %s" % (output_file, f)
        start = datetime.datetime.now()
        call(["convert", f,
              "-resize", "300x300",
              output_file])
        time_taken = (datetime.datetime.now() - start).total_seconds()
        print "Conversion complete in %d seconds." % (time_taken, )
