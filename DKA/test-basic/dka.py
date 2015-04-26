#!/usr/bin/env python3

#DKA:xvrabe07

import sys
import params
import parser
import fsm

#create object "param" to work with arguments
param = params.Param(sys.argv)

try:
    parsed = param.parse() # try to parse parameters
    if parsed == 'helpmsg': # if arguments include only '--help' prin help page
        raise Exception(0)
    parsedParams = param.getParams() # get parsed parameters

    parser = parser.Parser(param.read(),parsedParams['i']) # create object parser for parsing fsm
    parsed = parser.parseFsm() # try to parse fsm

    if parsed == 2: # if parsing end with return code '2' -> syntax error raise 40
        raise Exception(40)

    # if parsing end with 1 parsing is OK
    if parsed == 1:
        fsm = fsm.Fsm(parser.getFsmStates(),parser.getFsmAlphabet(),parser.getFsmRules(),parser.getFsmInitState(),parser.getFsmFinishStates()) # create object fsm for future operations with fsm
        if parsedParams['e'] == True: # remove epsilon rules
            fsm.removeEps()
        if parsedParams['d'] == True: # determinize fsm
            fsm.removeEps()
            fsm.determinize()
        fsm.write(param.outputHandler()) # write fsm to output file
        param.closeOutputHandler() # close output file

except Exception as e: # if error, exit with prober exit code
    exit(e.args[0])

exit(0) # all is OK
