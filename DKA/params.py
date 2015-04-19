#!/usr/bin/env python3

import sys
import re

#
# Class for parsing arguments, working with input/output files
#
class Param:
    inputFile = False
    outputFile = False
    e = False # if set remove eps
    d = False # if set determinize
    i = False # if set case insensitive
    arguments = False # raw argumnents

    def __init__(self,arguments):
        self.arguments = arguments
    #
    # Method for parse arguments
    #
    def parse(self):
        for argument in self.arguments:

            # first argument must be name of script
            if(argument == 'dka.py'):
                continue

            # if is set only '--help' write help msg.
            if((argument == '--help' or argument == '-h') and len(self.arguments) == 2):
                PrintHelpMsg()
                return 'helpmsg'
            # if is not '--help' only argument return exit 1
            if((argument == '--help' or argument == '-h') and len(self.arguments) != 2):
                raise Exception(1)
                continue

            # set param e
            if(argument == '--no-epsilon-rules' or argument == '-e'):
                if(self.d == True):
                    raise Exception(1)
                elif(self.e == True):
                    raise Exception(1)
                else:
                    self.e = True
                continue

            # set param d
            if(argument == '--determinization' or argument == '-d'):
                if(self.e == True):
                    raise Exception(1)
                elif(self.d == True):
                    raise Exception(1)
                else:
                    self.d = True
                continue

            # set param i
            if(argument == '--case-insensitive' or argument == '-i'):
                if(self.i == True):
                    raise Exception(1)
                else:
                    self.i = True
                continue

            # parse path to output file
            catch = re.match('^--output=(.+)$',argument)
            if(catch):
                if(self.outputFile != False):
                    raise Exception(1)
                else:
                    self.__openOutput(catch.group(1))
                continue

            # parse path to input file
            catch = re.match('^--input=(.+)$',argument)
            if(catch):
                if(self.inputFile != False):
                    raise Exception(1)
                else:
                    self.__openInput(catch.group(1))
                continue

            else:
                raise Exception(1)

    #
    # Method for open input file
    #
    def __openInput(self,name):
        try: # open input file for reading
            self.inputFile = open(name,mode = 'r', encoding = 'utf-8')
        except:
            raise Exception(2)

    #
    # Method for open output file
    #
    def __openOutput(self,name):
        try: # open output file for writing
            self.outputFile = open(name,mode = 'w', encoding = 'utf-8')
        except:
            raise Exception(3)

    #
    # Method for return dir with arguments
    #
    def getParams(self):
        # get dir with arguments
        params = {'e' : self.e, 'd' : self.d, 'i' : self.i, 'inputFile': self.inputFile, 'outputFile': self.outputFile}
        return params

    #
    # Method for read input file
    #
    def read(self):
        if(self.inputFile == False):
            self.inputFile = sys.stdin
        return self.inputFile.read()

    #
    # Method return output file hander
    #
    def outputHandler(self):
        if(self.outputFile == False):
            self.outputFile = sys.stdout
        return self.outputFile

    #
    # Method for close output handler
    #
    def closeOutputHandler(self):
        self.outputFile.close()

#
# Function for print help msg
#
def PrintHelpMsg():
    print("HELP MSG")
