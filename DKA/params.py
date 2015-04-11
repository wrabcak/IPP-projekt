#!/usr/bin/env python3

import sys
import re

class Param:
    inputFile = False
    outputFile = False
    e = False
    d = False
    i = False
    arguments = False

    def __init__(self,arguments):
        self.arguments = arguments

    def parse(self):
        for argument in self.arguments:

            if(argument == 'dka.py'):
                continue

            if((argument == '--help' or argument == '-h') and len(self.arguments) == 2):
                PrintHelpMsg()
                return 'helpmsg'
            if((argument == '--help' or argument == '-h') and len(self.arguments) != 2):
                raise Exception(1)
                continue

            if(argument == '--no-epsilon-rules' or argument == '-e'):
                if(self.d == True):
                    raise Exception(1)
                elif(self.e == True):
                    raise Exception(1)
                else:
                    self.e = True
                continue

            if(argument == '--determinization' or argument == '-d'):
                if(self.e == True):
                    raise Exception(1)
                else:
                    self.d = True
                continue

            if(argument == '--case-insensitive' or argument == '-i'):
                self.i = True
                continue

            catch = re.match('^--output=(.+)$',argument)
            if(catch):
                if(self.outputFile != False):
                    raise Exception(1)
                else:
                    self.__openOutput(catch.group(1))
                continue

            catch = re.match('^--input=(.+)$',argument)
            if(catch):
                if(self.inputFile != False):
                    raise Exception(1)
                else:
                    self.__openInput(catch.group(1))
                continue

            else:
                raise Exception(1)

    def __openInput(self,name):
        try:
            self.inputFile = open(name,mode = 'r', encoding = 'utf-8')
        except:
            raise Exception(2)

    def __openOutput(self,name):
        try:
            self.outputFile = open(name,mode = 'w', encoding = 'utf-8')
        except:
            raise Exception(3)

    def getParams(self):
        params = {'e' : self.e, 'd' : self.d, 'i' : self.i, 'inputFile': self.inputFile, 'outputFile': self.outputFile}
        return params

    def read(self):
        if(self.inputFile == False):
            self.inputFile = sys.stdin
        return self.inputFile.read()

    def outputHandler(self):
        if(self.outputFile == False):
            self.outputFile = sys.stdout
        return self.outputFile

    def closeOutputHandler(self):
        self.outputFile.close()

def PrintHelpMsg():
    print("HELP MSG")
