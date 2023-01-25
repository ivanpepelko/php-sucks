#!/usr/bin/env bash

#url=https://imr3yvkgdd.eu-west-1.awsapprunner.com # amp
#url=https://iyspwdmpxd.eu-west-1.awsapprunner.com # amp-StreamSelectDriver
url=https://aau4zk8hg9.eu-west-1.awsapprunner.com # node
#url=https://7tbpbkp4ap.eu-west-1.awsapprunner.com # classic

duration=10
proc=$(nproc)
threads=$(($proc/3))
connections=$((2*"$threads"))

trap "exit 1" INT

for path in {/,/cpu,/write,/read}; do
    wrk -d$duration -t"$threads" -c$connections "$url""$path"
    echo
done
