rm ./tasks/*-task.json
rm ./tasks/*/*-task.json
#Clear context directories, avoiding syntax which may delete targets of symlinks
find ./tasks -mindepth 1 -maxdepth 1 -type d -exec rm -r {} \;

rm ./credentials/*-credentials.json
rm ./credentials/transient/*.json
