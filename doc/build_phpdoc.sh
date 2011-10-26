#!/bin/bash

doc_basedir=`pwd`

for docdir in core contrib local
do
    if [ -d ../$docdir ]
    then
        cd ../$docdir && phpdoc -d . -t $doc_basedir/$docdir -pp
	cd -
    fi
done
