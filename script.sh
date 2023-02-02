#!/bin/bash

if [ -z "$1" ]
then
        echo "Error! No argument supplied"
        exit
elif [ $1 = "all" ]
then
    cp Params.automacao.php Params.php
    php Main.php 2>&1 > logs/log.automacao.txt
    cp Params.mecanica.php Params.php
    php Main.php 2>&1 > logs/log.mecanica.txt
    cp Params.infra.php Params.php
    php Main.php 2>&1 > logs/log.infra.txt
else
    cp Params.$1.php Params.php
    php Main.php 2>&1 > logs/log.$1.txt
fi


