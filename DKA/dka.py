#!/usr/bin/env python3

#DKA:xvrabe07

import sys
import params

param = params.Param(sys.argv)
try:
    param.parse()
    param.read()
except Exception as e:
    exit(e.args[0])

exit(0)
