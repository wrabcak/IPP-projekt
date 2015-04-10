
green='\033[0;32m'
red='\033[0;31m'
NC='\033[0m'

for i in {01..22}
do
    echo -e "${red}Test: $i${NC}"
    diff test/ref-out/test$i.out test/test$i.out
    if [ "$?" = "0" ]; then
        echo -e "${green}Test: $i is [OK]${NC}"
    fi

done
