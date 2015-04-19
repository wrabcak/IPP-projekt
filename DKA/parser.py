#!/usr/bin/env python3

import re

#
# Class for store rule
#
class Rule:
    fromState = False
    symbol = False
    toState = False

    def __init__(self,fromState,symbol,toState):
        # load fromState, symbol and toState to object
        self.fromState = fromState
        self.symbol = symbol
        self.toState = toState

    def __str__(self):
        return str(self.__dict__)

    def __eq__(self, other):
        return self.__dict__ == other.__dict__

#
# Class for parsing input fsm
#
class Parser:
    inputFile = False
    caseInsensitive = False
    inputFileLength = False

    __fsmStates = list() # list of states in fsm
    __fsmAlphabet = list() # list of alphabet
    __fsmRules = list() # list of Rule objects
    __fsmInitState = False # init state of fsm
    __fsmFinishStates = list() # list of final states

    def __init__(self,inputFile,caseInsensitive):
        self.inputFile = inputFile
        self.caseInsensitive = caseInsensitive
        self.inputFileLength = len(self.inputFile)

    #
    # Method to make all letters low
    #
    def __lowerCase(self):
        if(self.caseInsensitive == True):
            self.inputFile = self.inputFile.lower()

    #
    # Method to skip all white spaces
    #
    def __skipWhiteSpaces(self,index):
        # incerement index until related char is space
        while self.inputFile[index].isspace():
            index = index + 1
        return index

    #
    # Method to skip all comments
    #
    def __skipComments(self,index):
        # increment index until end of comment
        if self.inputFile[index] == '#':
            while self.inputFile[index] != '\n':
                index = index + 1
            return index+1
        return index

    #
    # Method is calling skipWhitespaces and skipComments
    #
    def __skip(self,index):
        try:
            while self.inputFile[index].isspace() or self.inputFile[index] == '#':
                index = self.__skipWhiteSpaces(index)
                index = self.__skipComments(index)
            return index
        except:
            raise Exception(40)

    #
    # Method parsing states of fsm, return list of states
    #
    def __getStates(self,index):
        fileLength = self.inputFileLength # get length of input file

        # repeat until end of input file
        while(index < fileLength):
            index = self.__skip(index) # skip
            state = re.match('^([a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?)',self.inputFile[index:]) # parse state from input file

            if not state: # if state is not found -> syntax error
                raise Exception(40)

            # add new state to fsmStates list
            self.__fsmStates.append(state.group(0))
            index = index + len(state.group(0)) # increment index to end of state length

            index = self.__skip(index) # skup

            # after state must by comma if its not last state
            if self.inputFile[index] == ',':
                index = index + 1
                continue
            index = self.__skip(index) # skip
            # if its last state end parsing
            if self.inputFile[index] == '}':
                return index
            else: # not '}' or ',' -> syntax error
                raise Exception(40)
        # during parsing states found some wrong char -> syntax error
        raise Exception(40)

    def __getFinishStates(self,index):
        fileLength = self.inputFileLength # get length of input file

        # repeat until end of input file
        while(index < fileLength):
            index = self.__skip(index) # skip
            match = re.match('^([a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?)',self.inputFile[index:]) # parse final state from input file

            if not match: # if final state is not found -> syntax error
                raise Exception(40)

            finishState = match.group(0)

            # if final state is not in states -> semantic error
            if finishState not in self.__fsmStates:
                raise Exception(41)

            # add new final state to list of final states
            self.__fsmFinishStates.append(finishState)
            index = index + len(finishState)

            # after state must by comma if its not last state
            if self.inputFile[index] == ',':
                index = index + 1
                continue
            index = self.__skip(index) # skip

            # if its last state end parsing
            if self.inputFile[index] == '}':
                return index
            else:
                raise Exception(40)

        # during parsing final states found some wrong char -> syntax error
        raise Exception(40)
    #
    # Method to parse one symbol from alphabet
    #
    def __parseInputSymbol(self,symbol):

        # if symbol is "''" return "'"
        if symbol[0] == "'" and symbol[1] == "'" and symbol[2] == "'":
            return "'"

        # if symbol is epsilon return empty symbol
        if symbol[0] == "'" and symbol[1] == "'":
            return 'eps'

        #first and third char must be "'" second is input symbol and third  again "'"
        if symbol[0] == "'" and symbol[2] == "'":
            return symbol[1]
        else: # if found wrong input symbol -> syntax error
            raise Exception(40)
        # if not found any input symbol -> syntax error
        raise Exception(40)

    #
    # Method to parse alphabet
    #
    def __getAlphabet(self,index):
        fileLength = self.inputFileLength # get length of input file

        # repeat until end of input file
        while(index < fileLength):
            index = self.__skip(index) # skip
            inputSymbol = self.inputFile[index:index+3] # load input symbol fomr input file
            symbol = self.__parseInputSymbol(inputSymbol) # parse input symbol

            if symbol == "'":
                self.inputFile = self.inputFile[:index] + self.inputFile[index+1:] # if symbol is "'" remove one ' in input file.
            index = index + len(inputSymbol) # skip
            self.__fsmAlphabet.append(symbol) # add new symbol tu alphabed

            index = self.__skip(index) # skup

            # after state must by comma if its not last symbol
            if self.inputFile[index] == ',':
                index = index + 1
                continue

            # if its last symbol end parsing
            if self.inputFile[index] == '}':
                return index
            else:
                raise Exception(40)

        raise Exception(40)

    #
    # Method to parse rules
    #
    def __getRules(self,index):
        fileLength = self.inputFileLength

        fromState = None
        toState = None
        symbol = None

        while(index < fileLength):
            index = self.__skip(index)
            match = re.match('^([a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?)',self.inputFile[index:]) # parse fromState from rule in input file.
            if not match:
                raise Exception(40)

            fromState = match.group(0)

            if fromState not in self.__fsmStates: # if from state isn't in list of states
                raise Exception(41)

            index = index + len(fromState)
            index = self.__skip(index)

            if self.inputFile[index:index+2] == '->': # if is next symbol in input fsm '->' input symbol is epsilon
                symbol = 'eps' # symbol is epsilon
            else:
                inputSymbol = self.inputFile[index:index+3]
                symbol = self.__parseInputSymbol(inputSymbol) # parse input symbol in rule in fsm

                if symbol == "'":
                    self.inputFile = self.inputFile[:index] + self.inputFile[index+1:] # if is input symbol "'" remove one apostrof
                    index = index + 3
                elif symbol == 'eps':
                    index = index + 2
                else:
                    index = index + len(inputSymbol)

            if symbol != 'eps' and symbol not in self.__fsmAlphabet: # if symbol is not in list of alphabet or is not epsilon -> semantic rule
                raise Exception(41)

            index = self.__skip(index) # skip

            if self.inputFile[index:index+2] == '->': # next symbol must be '->' in rule.
                 index = index + 2
                 index = self.__skip(index)
                 match = re.match('^([a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?)',self.inputFile[index:]) # parse toState in rule
                 if not match: # if not match any toState -> syntax error
                     raise Exception(40)
                 toState = match.group(0)

                 if toState not in self.__fsmStates: # if toState in rule is not in list of states -> semantic error
                     raise Exception(41)

                 index = index + len(toState)
                 index = self.__skip(index)

            else: # if next symbol in rule is not '->' -> syntax error
                raise Exception(40)

            actualRule = Rule(fromState,symbol,toState) # create rule object
            self.__fsmRules.append(actualRule) # append rule in list of rules

            index = self.__skip(index) # skip

            if self.inputFile[index] == ',': # after rule next char must be ',' if its not lat rule
                index = index + 1
                continue

            if self.inputFile[index] == '}': # if last rule next char is '}'
                return index
            else: # if not -> syntax error
                raise Exception(40)

    #
    # Method to parse init state
    #
    def __getInitState(self,index):
        index = self.__skip(index)

        match = re.match('^([a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?)',self.inputFile[index:]) # match init state in input file
        if not match: # if not found any -> syntax error
            raise Exception(40)

        initState = match.group(0)

        if initState not in self.__fsmStates: # if initState is not in list of states -> semantic error
            raise Exception(41)

        index = index + len(initState)
        index = self.__skip(index)

        if self.inputFile[index] != ',': # if next chars is not ',' ->  syntax error
            raise Exception(40)

        self.__fsmInitState = initState # set parsed state as initState
        return index

    #
    # Method for parsing input fsm
    #
    def parseFsm(self):
        index = 0

        self.__lowerCase()
        index = self.__skip(index)

        if self.inputFile[index] != '(': # first char must be '('
            raise Exception(40)
        index = index + 1
        index = self.__skip(index)

        if self.inputFile[index] == '{': # open list of states
            index = index +1
            index = self.__skip(index)
            if self.inputFile[index] == '}': #if list is empty next char is '}'
                index = index + 1
            else: # if is list of states not empty load all states
                index = self.__getStates(index)
        else:
            raise Exception(40)

        index = index + 1
        index = self.__skip(index)
        if self.inputFile[index] != ',':
            raise Exception(40)
        index = index + 1
        index = self.__skip(index)

        if self.inputFile[index] == '{': # next list is alphabet
            index = index +1
            index = self.__skip(index)
            if self.inputFile[index] == '}':
                raise Exception(41)
            else:
                index = self.__getAlphabet(index) # if list of input symbol is not empty, load all symbols
        else:
            raise Exception(40)

        index = index + 1
        index = self.__skip(index)
        if self.inputFile[index] != ',':
            raise Exception(40)
        index = index + 1
        index = self.__skip(index)

        if self.inputFile[index] == '{': # next list are rules
            index = index +1
            index = self.__skip(index)
            if self.inputFile[index] != '}':
                index = self.__getRules(index) # if list of rules are not empty, load all rules

        index = index + 1
        index = self.__skip(index)
        if self.inputFile[index] != ',':
            raise Exception(40)
        index = index + 1
        index = self.__skip(index)

        index = self.__getInitState(index) # load init state of FSM

        index = index + 1
        index = self.__skip(index)

        if self.inputFile[index] == '{': # next list are list of final states
            index = index +1
            index = self.__skip(index)

            if self.inputFile[index] == '}':
                index = index + 1
            else:
                index = self.__getFinishStates(index) # if list of final states are not empty, load all final states
        else:
            raise Exception(40)

        if self.inputFile[index] == '}':
            index = index + 1

        index = self.__skip(index)

        if self.inputFile[index] == ')': # fsm must end with ')' if not or if there any other chars -> syntax error
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
        if self.inputFile[index] != ')':
            raise Exception(40)

    #
    # Method to get all fsm states
    #
    def getFsmStates(self):
        return self.__fsmStates

    #
    # Method to get all fsm rules
    #
    def getFsmRules(self):
        return self.__fsmRules

    #
    # Method to get all fsm alphabet
    #
    def getFsmAlphabet(self):
        return self.__fsmAlphabet

    #
    # Method to get init fsm state
    #
    def getFsmInitState(self):
        return self.__fsmInitState

    #
    # Method to get all fsm final states
    #
    def getFsmFinishStates(self):
        return self.__fsmFinishStates
