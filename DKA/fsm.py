#!/usr/bin/env python3

import sys
import re
from operator import attrgetter
import parser

#
# Class for operations with FSM
#
class Fsm:

    __states = False
    __alphabet = False
    __rules = False
    __initState = False
    __finishStates = False
    __closure = {}

    def __init__(self,states,alphabet,rules,initState,finishStates):
        # load all variables
        self.__states = states
        self.__alphabet = alphabet
        self.__rules = rules
        self.__initState = initState
        self.__finishStates = finishStates


    #
    # Method to sort rules
    #
    def __sortRules(self):
        # sorting: primary key: fromState, secondary: symbol and last is toState
        self.__rules.sort(key=attrgetter('fromState', 'symbol', 'toState'))

    #
    # Method to make closure
    #
    def __epsClo(self):
        for state in self.__states: # repeat for every state
            Qj = set() # Qj is Q(i-1) state in IFJ04 lecture
            Qj.add(state)

            while True:
                Qi = set(Qj)
                for rule in self.__rules:
                    for state2 in Qi:
                        if rule.fromState == state2 and rule.symbol == 'eps': # if rule fromState is in Qi set and it's epsilon rule add to Qj list
                            Qj.add(rule.toState)
                if Qi.issubset(Qj) and Qj.issubset(Qi): # if Qi is same as Qj, break fom while
                    break
            self.__closure[state] = Qi # add closure for related state

    #
    # Method to remove epsilon rules
    #
    def removeEps(self):
        self.__epsClo() # firt, epislon closure is needed
        newRules = list() # list for new rules
        newFinishStates = list() # list for new final States
        for state in self.__closure: # for every state in list of closure
            for closure in self.__closure[state]: # for every state in states from closure
                for element in self.__rules: # element is Rule object
                    if element.fromState == closure and element.symbol != 'eps': # if fromState from rule is in closure and its not epsilon rule, create new Rule
                        newState = parser.Rule(state,element.symbol,element.toState) # Create new object Rule with new attributes
                        newRules.append(newState) #append to new rules list
                if self.__closure[state].intersection(self.__finishStates): # if is state in finalStates and also in closure, add this state to final states
                    newFinishStates.append(state)

        self.__rules = newRules # replace rules with new rules after remove epsilon rules
        self.__finishStates = newFinishStates # replace final states after remove epsilon rules

    def determinize(self):
        dictionary = {}

        if len(self.__rules) == 0: # if is list with rules empty,
            self.__states = self.__initState # set of states is only init state
            if self.__initState not in self.__finishStates: # if init states is not in list of final states, set with final states will be empty
                self.__finishStates = set()
            if self.__initState in self.__finishStates: # if init state is in final states
                self.__finishStates = self.__initState # final state wil be only init state
            return # exit determinization

        # this is transformation for list of rules to add all rules to one directory
        for rule in self.__rules:
            fromState = rule.fromState
            symbol = rule.symbol
            array = {}
            for subrule in self.__rules:
                if fromState == subrule.fromState and symbol == subrule.symbol: # find rule with same fromState and symbol attribute
                    if symbol not in array: # if this symbol is not in array, add symbol and to rule to temporary array
                        array[symbol] = set((subrule.toState,))
                    else: # if is symbol in array, just update items with new toState state
                        array[symbol].update((subrule.toState,))
            if fromState not in dictionary.keys(): # if from state key is not in directory, add to directory with set of symbol and toStates
                dictionary[fromState] = array
            else: # if is in directory, just update set of symbol nad toStates with new temporarry array
                dictionary[fromState].update(array)

        if dictionary == {}: # if is directory empty, return error
            return

        for state in self.__states:
            if state not in dictionary.keys(): # if state is not in directory, add this element to direcotry, where state is fromState without symbol and toStates
                dictionary[state] = {}


        # init temporary lists and dirs for new states and rules
        determinizedStates = list()
        determinizedRules = {}
        determinizedFinalStates = list()
        newStates = []
        newStates.append(self.__initState)

        while len(newStates) > 0: # while there is a new state
            mergedState = newStates.pop() # get newState
            temporaryRules = {}
            divisionStates = mergedState.split('_') # split states if are joined

            for state in divisionStates: # for every state in joined state
                for symbol in dictionary[state]: # find set with to states
                    toStates = dictionary[state][symbol] # get all toStates related to fromStates from joined states and related symbol
                    if symbol in temporaryRules: # if symbol is in temporary rules, update to symbol with toStates
                        temporaryRules[symbol].update(toStates)
                    else:
                        temporaryRules.update({symbol : set(toStates)}) # if symbol is not in temporary rules, add symbol and actual toStates states

            for symbol in temporaryRules: # for every symbol in temporary rules
                toJoin = sorted(temporaryRules[symbol]) # get sorted toStates related to symbol
                joinState = '_'.join(toJoin) # join all these states
                if mergedState in determinizedRules: # if mergedState is in determinizedRules
                    determinizedRules[mergedState][symbol] = joinState # add joinState to determinizedrules related to mergedstate and symbol
                else: # if not, create merged state in determinzied ruled and add joinState
                    determinizedRules.update({mergedState : {}})
                    determinizedRules[mergedState][symbol] = joinState

                if joinState not in determinizedStates: # if joinState is not in determinzed states, add it
                    newStates.append(joinState)

            for state in divisionStates: # for every state in splited states
                if state in self.__finishStates: # if splited state is final state
                   determinizedFinalStates.append(mergedState) # add joined state to final states

            determinizedStates.append(mergedState) # add merged state to determinized States

        self.__finishStates = determinizedFinalStates # replace final states with determinized final states

        # create new list of Rule objects
        newRules = list()
        for fromState, element in determinizedRules.items():
            for symbol, toState in element.items():
                newState = parser.Rule(fromState,symbol,toState) # create new object
                newRules.append(newState) # add it to list of Rule objects

        self.__rules = newRules # replace rules with new determinized Rules

        newStates = list()
        for rule in self.__rules: # Add new determinized states to list of states
            if rule.fromState not in newStates:
                newStates.append(rule.fromState)
            if rule.toState not in newStates:
                newStates.append(rule.toState)
        self.__states = newStates # replace states with new determinized States

    #
    # Method to write final fsm to output file
    #
    def write(self,outputHandler):
        self.__states = list(set(self.__states))
        self.__alphabet = list(set(self.__alphabet))
        self.__finishStates = list(set(self.__finishStates))
        outputHandler.write('(\n') # first char must by open bracket
        self.__states.sort()
        outputHandler.write('{') # then curly bracket
        for state in self.__states: # then write all states
            outputHandler.write(state)
            if state != self.__states[-1]: # if it's not last state print also comma
                outputHandler.write(', ')
        outputHandler.write('},\n') # after all states write closed curly bracket

        self.__alphabet.sort() # sort alphabet before printing
        outputHandler.write('{') # start with opened curly bracket
        for alphabet in self.__alphabet: # write all symbols
            outputHandler.write("'")
            if alphabet == "'":
                outputHandler.write("''")
            else:
                outputHandler.write(alphabet)
            if alphabet != self.__alphabet[-1]: # if its not last symbol print also comma
                outputHandler.write("', ")
            else:
                outputHandler.write("'")
        outputHandler.write("},\n") # close it with curly bracket

        outputHandler.write('{\n')
        for rule in self.__rules:
            if rule.symbol == 'eps': # if rule including epsilon rename epsilon rule as '0'
                rule.symbol = '0'
        self.__sortRules() # sort rules
        self.__removeDupesRules() # remove dulicate rules
        for rule in self.__rules: # rename all '0' rules as epsilon rules. Needed for proper sorting
            if rule.symbol == '0':
                rule.symbol = 'eps'
        for rule in self.__rules: # print all rules
            outputHandler.write(rule.fromState)
            outputHandler.write(" '")
            if rule.symbol == 'eps': # if is epsilon rules, don't write any symbol
                outputHandler.write("")
            elif rule.symbol == "'":
                outputHandler.write("''")
            else:
                outputHandler.write(rule.symbol)
            outputHandler.write("' ")
            outputHandler.write("-> ")
            outputHandler.write(rule.toState)
            if rule != self.__rules[-1]: # if not last rule write also comma
                outputHandler.write(",")
            outputHandler.write("\n")
        outputHandler.write("},\n")

        outputHandler.write(self.__initState + ',\n') # write init state

        outputHandler.write('{')

        self.__finishStates.sort() # sort final states
        for state in self.__finishStates: # print all final states
            outputHandler.write(state)
            if state != self.__finishStates[-1]: # if not last final state, print also comma
                outputHandler.write(', ')
        outputHandler.write('}\n')

        outputHandler.write(')') # close fsm

    #
    # Method to remove duplicate rules in list of rules
    #
    def __removeDupesRules(self):
        dupeRules = list()
        for rule in self.__rules: # list all rules
            if rule not in dupeRules: # check if is same rule in list of rules
                dupeRules.append(rule) # if not, append it to temporary list of rules
        self.__rules = dupeRules
