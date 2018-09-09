docker container run -d -p 80:80 -v $(pwd):/var/www/html --name convertor-server fauria/lamp
docker container run -d -p 80:80 -v $(pwd):/var/www/html --name convertor-server p3rg3l40/convertor-server