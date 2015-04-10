#!/usr/bin/env python3

import sys
import re
from operator import attrgetter
import parser

class Fsm:

    __states = False
    __alphabet = False
    __rules = False
    __initState = False
    __finishStates = False
    __closure = {}

    def __init__(self,states,alphabet,rules,initState,finishStates):
        self.__states = states
        self.__alphabet = alphabet
        self.__rules = rules
        self.__initState = initState
        self.__finishStates = finishStates


    def __sortRules(self):
        self.__rules.sort(key=attrgetter('fromState', 'symbol', 'toState'))

    def __epsClo(self):
        for state in self.__states:
            Qj = set()
            Qj.add(state)

            while True:
                Qi = Qj
                for rule in self.__rules:
                    if rule.fromState == state and rule.symbol == 'eps':
                        Qi.add(rule.toState)
                if Qi == Qj:
                    break
            self.__closure[state] = Qi

    def removeEps(self):
        self.__epsClo()
        newRules = list()
        for state in self.__closure:
            for closure in self.__closure[state]:
                if closure != state:
                   for element in self.__rules:
                       if element.fromState == closure:
                           newState = parser.Rule(state,element.symbol,element.toState)
                           self.__rules.append(newState)

        for rule in self.__rules:
            if rule.symbol != 'eps':
                newRules.append(rule)

        self.__rules = newRules

    def determinize(self):
        dictionary = {}
        for rule in self.__rules:
            fromState = rule.fromState
            symbol = rule.symbol
            array = {}
            for subrule in self.__rules:
                if fromState == subrule.fromState and symbol == subrule.symbol:
                    if symbol not in array:
                        array[symbol] = set((subrule.toState,))
                    else:
                        array[symbol].update((subrule.toState,))
            if fromState not in dictionary.keys():
                dictionary[fromState] = array
            else:
                dictionary[fromState].update(array)

        if dictionary == {}:
            return

        for state in self.__states:
            if state not in dictionary.keys():
                dictionary[state] = {}

        determinizedStates = list()
        determinizedRules = {}
        determinizedFinalStates = list()
        newStates = []
        newStates.append(self.__initState)
        end = False

        while len(newStates) > 0:
            mergedState = newStates.pop()
            determinizedStates.append(mergedState)
            end = False
            temporaryRules = {}

            for state in mergedState.split('_'):
                if state in self.__finishStates:
                   end = True

                for symbol, toStates in dictionary[state].items():
                    temporaryRules.setdefault(symbol,set()).update(toStates)
                #print(temporaryRules)
            for symbol, toStates in temporaryRules.items():
                mergedToStates = '_'.join(sorted(toStates))
                determinizedRules.setdefault(mergedState,{})[symbol]=mergedToStates

                if mergedToStates not in determinizedStates:
                    newStates.append(mergedToStates)
            if end:
                determinizedFinalStates.append(mergedState)

        self.__finishStates = determinizedFinalStates

        newRules = list()
        for fromState, element in determinizedRules.items():
            for symbol, toState in element.items():
                newState = parser.Rule(fromState,symbol,toState)
                newRules.append(newState)

        self.__rules = newRules

        newStates = list()
        for rule in self.__rules:
            if rule.fromState not in newStates:
                newStates.append(rule.fromState)
            if rule.toState not in newStates:
                newStates.append(rule.toState)
        self.__states = newStates

    def write(self,outputHandler):
        self.__states = list(set(self.__states))
        self.__alphabet = list(set(self.__alphabet))
        self.__finishStates = list(set(self.__finishStates))
        outputHandler.write('(\n')
        self.__states.sort()
        outputHandler.write('{')
        for state in self.__states:
            outputHandler.write(state)
            if state != self.__states[-1]:
                outputHandler.write(', ')
        outputHandler.write('},\n')


        self.__alphabet.sort()
        outputHandler.write('{')
        for alphabet in self.__alphabet:
            outputHandler.write("'")
            if alphabet == "'":
                outputHandler.write("''")
            else:
                outputHandler.write(alphabet)
            if alphabet != self.__alphabet[-1]:
                outputHandler.write("', ")
            else:
                outputHandler.write("'")
        outputHandler.write("},\n")

        outputHandler.write('{\n')
        self.__sortRules()
        for rule in self.__rules:
            outputHandler.write(rule.fromState)
            outputHandler.write(" '")
            if rule.symbol == 'eps':
                outputHandler.write("")
            elif rule.symbol == "'":
                outputHandler.write("''")
            else:
                outputHandler.write(rule.symbol)
            outputHandler.write("' ")
            outputHandler.write("-> ")
            outputHandler.write(rule.toState)
            if rule != self.__rules[-1]:
                outputHandler.write(",")
            outputHandler.write("\n")
        outputHandler.write("},\n")

        outputHandler.write(self.__initState + ',\n')

        outputHandler.write('{')
        self.__finishStates.sort()
        for state in self.__finishStates:
            outputHandler.write(state)
            if state != self.__finishStates[-1]:
                outputHandler.write(', ')
        outputHandler.write('}\n')

        outputHandler.write(')')
