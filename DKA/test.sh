
green='\033[0;32m'
red='\033[0;31m'
NC='\033[0m'

for i in {01..10}
do
    echo -e "${red}Test: $i${NC}"
    diff test-basic/ref-out/test$i.out test-basic/test$i.out
    diff test-basic/ref-out/test$i.!!! test-basic/test$i.!!!
    if [ "$?" = "0" ]; then
        echo -e "${green}Test: $i is [OK]${NC}"
    fi
done
for i in {90..92}
do
    echo -e "${red}Test: $i${NC}"
    diff test-basic/ref-out/test$i.out test-basic/test$i.out
    diff test-basic/ref-out/test$i.!!! test-basic/test$i.!!!
    if [ "$?" = "0" ]; then
        echo -e "${green}Test: $i is [OK]${NC}"
    fi
done
