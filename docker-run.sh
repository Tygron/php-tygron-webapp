docker-compose down
docker-compose -f docker-compose.yaml -f docker-compose-override.yaml up -d "$@"
cat docker-compose-override.yaml
