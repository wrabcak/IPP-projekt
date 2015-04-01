#!/usr/bin/env python3

class Parser:
    inputFile = False
    caseInsensitive = False
    inputFileLength = False

    def __init__(self,inputFile,caseInsensitive):
        self.inputFile = inputFile
        self.caseInsensitive = caseInsensitive
        self.inputFileLength = len(self.inputFile)

    def parsefsm(self):

