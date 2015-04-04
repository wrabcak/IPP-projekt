#!/usr/bin/env python3

import re

class Parser:
    inputFile = False
    caseInsensitive = False
    inputFileLength = False

    __fsmStates = set()
    __fsmAlphabet = set()
    __fsmRules = {}

    def __init__(self,inputFile,caseInsensitive):
        self.inputFile = inputFile
        self.caseInsensitive = caseInsensitive
        self.inputFileLength = len(self.inputFile)

    def __lowerCase(self):
        if(self.caseInsensitive == True):
            self.inputFile = self.inputFile.lower()

    def __skipWhiteSpaces(self,index):
        while self.inputFile[index].isspace():
            index = index + 1
        return index

    def __skipComments(self,index):
        if self.inputFile[index] == '#':
            while self.inputFile[index] != '\n':
                index = index + 1
            return index+1
        return index

    def __skip(self,index):
        index = self.__skipWhiteSpaces(index)
        index = self.__skipComments(index)
        return index

    def __getStates(self,index):
        fileLength = self.inputFileLength

        while(index < fileLength):
            index = self.__skip(index)
            state = re.match('^([a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?)',self.inputFile[index:])
            if not state:
                raise Exception(40)
            self.__fsmStates.add(state.group(0))
            index = index + len(state.group(0))

            if self.inputFile[index] == ',':
                index = index + 1
                continue
            index = self.__skip(index)
            if self.inputFile[index] == '}':
                return index

        raise Exception(40)

    def __parseInputSymbol(self,symbol):
        if symbol[0] == "'" and symbol[2] == "'":
            return symbol[1]
        else:
            raise Exception(40)
        raise Exception(40)

    def __getAlphabet(self,index):
        fileLength = self.inputFileLength

        while(index < fileLength):
            index = self.__skip(index)
            inputSymbol = self.inputFile[index:index+3]
            symbol = self.__parseInputSymbol(inputSymbol)

            index = index + len(inputSymbol)
            self.__fsmAlphabet.add(symbol)

            index = self.__skip(index)

            if self.inputFile[index] == ',':
                index = index + 1
                continue
            if self.inputFile[index] == '}':
                return index

        raise Exception(40)

    def parseFsm(self):
        index = 0

        self.__lowerCase()
        index = self.__skip(index)

        if self.inputFile[index] != '(':
            raise Exception(40)
        index = index + 1
        index = self.__skip(index)

        if self.inputFile[index] == '{':
            index = index +1
            index = self.__skip(index)
            if self.inputFile[index] == '}':
                index = index + 1
            else:
                index = self.__getStates(index)
        else:
            raise Exception(40)

        index = index + 1
        index = self.__skip(index)
        if self.inputFile[index] != ',':
            raise Exception(40)
        index = index + 1
        index = self.__skip(index)

        if self.inputFile[index] == '{':
            index = index +1
            index = self.__skip(index)
            if self.inputFile[index] == '}':
                raise Exception(41)
            else:
                index = self.__getAlphabet(index)
        else:
            raise Exception(40)

        print(self.__fsmStates)
        print(self.__fsmAlphabet)
