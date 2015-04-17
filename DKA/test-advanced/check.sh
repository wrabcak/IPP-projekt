#!/bin/bash

TASK=dka.py
INTERPRET="python3"
CONT=0
passed=0
failed=0
wsa=0

if [ -t 1 ]; then
    c_red=`tput setaf 1`
    c_green=`tput setaf 2`
    c_normal=`tput sgr0`
fi

function testdka() {  
 if [ $CONT -ne 0 ]; then
   if [ $CONT -eq $cnt_cont ]; then
     CONT=0;
   else
     cnt_cont=$[$cnt_cont+1]
     return
   fi
 fi
 
 if [ $2 -ne 0 ]; then
   touch ./test-advanced/tests/empty
   FILE="./test-advanced/tests/empty"
 elif ! [ -f ./test-advanced/tests/$1.out ]; then
   echo "${c_red}Mising test out: ./tests/$1.out $c_normal"
   return
 else
   FILE="./test-advanced/tests/$1.out"
 fi
 if ! [ -f ./test-advanced/tests/$1 ]; then
   echo "${c_red}Mising test input: ./tests/$1 $c_normal"
   return
 fi
 
 $INTERPRET $TASK --input=./test-advanced/tests/$1 --output=./test-advanced/tests/$1.tmp $3 2>/dev/null
 rcode=$?
 cnt=$[$cnt+1]
 fail=0;

   # kontrola navratoveho kodu
 if [ $2 -ne 0 ]; then
   if [ $rcode -eq $2 ]; then
    printf " - RETURN: ${c_green}pass$c_normal"
   else
    printf " - RETURN: ${c_red}FAIL$c_normal"
    fail=$[$fail+1];
   fi
 fi

   # kontrola vystupniho souboru
 if [ $2 -eq 0 ]; then
  if diff -q ./test-advanced/tests/$1.tmp $FILE > /dev/null; then
    printf " + OUTPUT: ${c_green}pass$c_normal"
    if [ -f ./test-advanced/tests/$1_ERROR ]; then
      rm ./test-advanced/tests/$1_ERROR
    fi
   else
    printf " + OUTPUT: ${c_red}FAIL$c_normal"
    fail=$[$fail+2];
    $INTERPRET $TASK --input=./test-advanced/tests/$1 --output=./test-advanced/tests/$1_ERROR $3
   fi
 fi
 printf "\t\t$1 $3\n"

 if [ $fail -ne 0 ]; then
  failed=$[$failed+1]
 else
  passed=$[$passed+1]
 fi

 if [[ $fail == 1 || $fail == 3 ]]; then
  echo "  expected: $2; returned: ${rcode}"
 fi

 cnt_cont=$[$cnt_cont+1]
}

#######################################################################
###########################   T E S T Y   #############################
#######################################################################

# NAME EXPECTED_RETURN
testdka "nopar1" 0 ""
testdka "determ1" 0 "-d"
testdka "determ2" 0 "-d"
testdka "determ3" 0 "-i -d"
testdka "determ4" 0 "-d"
testdka "eps1" 0 "-e"
testdka "eps2" 0 "-d"
testdka "eps3" 0 "-e"
testdka "fit1" 0 "-d"
testdka "fit2" 0 "-d"
testdka "fit3" 0 "-d"
testdka "fit4" 0 "-e"
testdka "fit5" 0 ""
testdka "fit6" 0 "-d"
testdka "fit7" 0 "-e"
testdka "fit8" 0 ""
testdka "fit9" 40 ""
testdka "01" 0 ""
testdka "02" 0 ""
testdka "03" 0 ""
testdka "04" 0 ""
testdka "05" 0 ""
testdka "06" 0 ""
testdka "10" 0 "--no-epsilon-rules"
testdka "11" 0 "-e"
testdka "20" 0 "--determinization"
testdka "21" 0 "-d"
testdka "22" 0 "-d"
testdka "23" 0 "-d --case-insensitive"
testdka "24" 0 "-d -i"
testdka "kompl" 0 "-d"
testdka "kompl1" 0 ""
testdka "kompl2" 0 "-e"
testdka "return1" 1 "--help"
testdka "return1" 1 "--help -e"
testdka "return1" 1 "-d -e"
testdka "return1" 1 "--no-epsilon-rules -e"
testdka "return2" 40 ""
testdka "return3" 40 ""
#~ testdka "return4" 2 "" # dle potreby vytvorit soubor bez prav
testdka "return5" 41 ""
testdka "return6" 41 "" #zadny pocatecni stav

if [ $passed -eq $cnt ]; then
  echo "DKA result $passed / $cnt"
else
  echo "DKA result ${c_red}$passed ${c_normal} / $cnt"
fi

cnt=0
passed=0
failed=0


rm ./tests/*.tmp
