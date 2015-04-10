#!/usr/bin/env python3

#DKA:xvrabe07

import sys
import params
import parser
import fsm

param = params.Param(sys.argv)

try:
    param.parse()
    parsedParams = param.getParams()

    parser = parser.Parser(param.read(),parsedParams['i'])
    parsed = parser.parseFsm()

    if parsed == 1:
        fsm = fsm.Fsm(parser.getFsmStates(),parser.getFsmAlphabet(),parser.getFsmRules(),parser.getFsmInitState(),parser.getFsmFinishStates())
        if parsedParams['e'] == True:
            fsm.removeEps()
        if parsedParams['d'] == True:
            fsm.removeEps()
            fsm.determinize()
        fsm.write(param.outputHandler())
        param.closeOutputHandler()


except Exception as e:
    exit(e.args[0])

exit(0)
