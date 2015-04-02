#!/usr/bin/env python3

ST_INIT = 0

class Parser:
    inputFile = False
    caseInsensitive = False
    inputFileLength = False

    def __init__(self,inputFile,caseInsensitive):
        self.inputFile = inputFile
        self.caseInsensitive = caseInsensitive
        self.inputFileLength = len(self.inputFile)

    def __lowerCase(self):
        if(caseInsensitive == True)
            self.inputFile = self.inputFile.lower()

    def __skipWhiteSpaces(self,index):
        while self.inputFile[index].isspace():
            index = index + 1
        return index

    def __skipComments(self,index):
        while self.inputFile[index] != '\n':
            index = index + 1
        return index

    def parseFsm(self):
        index = 0
        self.__lowerCase()
        index = __skipWhiteSpaces(index)
        index = __skipComments(index)

        if self.inputFile != '(':
            raise Exception(40)
        index = index + 1

        index = __skipWhiteSpaces(index)
        index = __skipComments(index)


