#!/usr/bin/env python3

import re

class Rule:
    fromState = False
    symbol = False
    toState = False

    def __init__(self,fromState,symbol,toState):
        self.fromState = fromState
        self.symbol = symbol
        self.toState = toState

    def __str__(self):
        return str(self.__dict__)

    def __eq__(self, other):
        return self.__dict__ == other.__dict__

class Parser:
    inputFile = False
    caseInsensitive = False
    inputFileLength = False

    __fsmStates = list()
    __fsmAlphabet = list()
    __fsmRules = list()
    __fsmInitState = False
    __fsmFinishStates = list()

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
        while self.inputFile[index].isspace() or self.inputFile[index] == '#':
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

            self.__fsmStates.append(state.group(0))
            index = index + len(state.group(0))

            index = self.__skip(index)

            if self.inputFile[index] == ',':
                index = index + 1
                continue
            index = self.__skip(index)

            if self.inputFile[index] == '}':
                return index
            else:
                raise Exception(40)

        raise Exception(40)

    def __getFinishStates(self,index):
        fileLength = self.inputFileLength

        while(index < fileLength):
            index = self.__skip(index)
            match = re.match('^([a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?)',self.inputFile[index:])
            if not match:
                raise Exception(40)

            finishState = match.group(0)

            if finishState not in self.__fsmStates:
                raise Exception(41)

            self.__fsmFinishStates.append(finishState)
            index = index + len(finishState)

            if self.inputFile[index] == ',':
                index = index + 1
                continue
            index = self.__skip(index)
            if self.inputFile[index] == '}':
                return index
            else:
                raise Exception(40)

        raise Exception(40)

    def __parseInputSymbol(self,symbol):


        if symbol[0] == "'" and symbol[1] == "'" and symbol[2] == "'":
            return "'"
        if symbol[0] == "'" and symbol[1] == "'":
            return 'eps'
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

            if symbol == "'":
                self.inputFile = self.inputFile[:index] + self.inputFile[index+1:]
            index = index + len(inputSymbol)
            self.__fsmAlphabet.append(symbol)

            index = self.__skip(index)

            if self.inputFile[index] == ',':
                index = index + 1
                continue
            if self.inputFile[index] == '}':
                return index
            else:
                raise Exception(40)

        raise Exception(40)

    def __getRules(self,index):
        fileLength = self.inputFileLength

        fromState = None
        toState = None
        symbol = None

        while(index < fileLength):
            index = self.__skip(index)
            match = re.match('^([a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?)',self.inputFile[index:])
            if not match:
                raise Exception(40)

            fromState = match.group(0)

            if fromState not in self.__fsmStates:
                raise Exception(41)

            index = index + len(fromState)
            index = self.__skip(index)

            if self.inputFile[index:index+2] == '->':
                symbol = 'eps'
            else:
                inputSymbol = self.inputFile[index:index+3]
                symbol = self.__parseInputSymbol(inputSymbol)

                if symbol == "'":
                    self.inputFile = self.inputFile[:index] + self.inputFile[index+1:]
                    index = index + 3
                elif symbol == 'eps':
                    index = index + 2
                else:
                    index = index + len(inputSymbol)

            if symbol != 'eps' and symbol not in self.__fsmAlphabet:
                raise Exception(41)

            index = self.__skip(index)

            if self.inputFile[index:index+2] == '->':
                 index = index + 2
                 index = self.__skip(index)
                 match = re.match('^([a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?)',self.inputFile[index:])
                 if not match:
                     raise Exception(40)
                 toState = match.group(0)

                 if toState not in self.__fsmStates:
                     raise Exception(41)

                 index = index + len(toState)
                 index = self.__skip(index)

            else:
                raise Exception(41)

            actualRule = Rule(fromState,symbol,toState)
            self.__fsmRules.append(actualRule)

            index = self.__skip(index)

            if self.inputFile[index] == ',':
                index = index + 1
                continue

            if self.inputFile[index] == '}':
                return index
            else:
                raise Exception(40)

    def __getInitState(self,index):
        index = self.__skip(index)

        match = re.match('^([a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?)',self.inputFile[index:])
        if not match:
            raise Exception(40)

        initState = match.group(0)

        if initState not in self.__fsmStates:
            raise Exception(41)

        index = index + len(initState)
        index = self.__skip(index)

        if self.inputFile[index] != ',':
            raise Exception(40)

        self.__fsmInitState = initState
        return index

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

        index = index + 1
        index = self.__skip(index)
        if self.inputFile[index] != ',':
            raise Exception(40)
        index = index + 1
        index = self.__skip(index)

        if self.inputFile[index] == '{':
            index = index +1
            index = self.__skip(index)
            if self.inputFile[index] != '}':
                index = self.__getRules(index)

        index = index + 1
        index = self.__skip(index)
        if self.inputFile[index] != ',':
            raise Exception(40)
        index = index + 1
        index = self.__skip(index)

        index = self.__getInitState(index)

        index = index + 1
        index = self.__skip(index)

        if self.inputFile[index] == '{':
            index = index +1
            index = self.__skip(index)

            if self.inputFile[index] == '}':
                index = index + 1
            else:
                index = self.__getFinishStates(index)
        else:
            raise Exception(40)

        if self.inputFile[index] == '}':
            index = index + 1

        index = self.__skip(index)

        if self.inputFile[index] == ')':
            try:
                while True:
                    index = index + 1
                    while self.inputFile[index].isspace():
                        index = index + 1
                    if self.inputFile[index] == "#":
                        while self.inputFile[index] != '\n':
                            index = index + 1
                    if self.inputFile[index].isspace() == 0 :
                        return 2
            except:
                    return 1

    def getFsmStates(self):
        return self.__fsmStates

    def getFsmRules(self):
        return self.__fsmRules

    def getFsmAlphabet(self):
        return self.__fsmAlphabet

    def getFsmInitState(self):
        return self.__fsmInitState

    def getFsmFinishStates(self):
        return self.__fsmFinishStates
