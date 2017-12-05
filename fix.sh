echo '### Create app/data path if not exist'
mkdir -p app/data

echo '### Validate by php-cs-fixer'
php-cs-fixer fix --config=.php_cs.dist -v --dry-run --using-cache=no --path-mode=intersection  `git diff --name-only --diff-filter=ACMRTUXB HEAD~..HEAD` > app/data/php-cs-fixer-problems
cat app/data/php-cs-fixer-problems

echo '### Auto fix error'
while read line
do
    if [  `echo $line | grep -e ')'`  ] ; then
        echo $line
    fi
done < app/data/php-cs-fixer-problems

echo '### Finished'
