
green='\033[0;32m'
red='\033[0;31m'
NC='\033[0m'

for i in {01..22}
do
    echo -e "${red}Test: $i${NC}"
    diff test/origin/test$i.out test/my_results/test$i.out
    if [ "$?" = "0" ]; then
        echo -e "${green}Test: $i is [OK]${NC}"
    fi

done
