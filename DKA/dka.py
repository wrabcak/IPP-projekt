#!/usr/bin/env python3

#DKA:xvrabe07

import sys
import params
import parser

param = params.Param(sys.argv)

try:
    param.parse()
    parsedParams = param.getParams()

    parser = parser.Parser(param.read(),parsedParams['i'])

except Exception as e:
    exit(e.args[0])

exit(0)
