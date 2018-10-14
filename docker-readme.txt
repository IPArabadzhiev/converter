docker container run -d -p 80:80 -v $(pwd):/var/www/html --name converter-server fauria/lamp
docker container run -d -p 80:80 -v $(pwd):/var/www/html --name converter-server p3rg3l40/converter-server

-- Cygwin
docker container run -d -p 80:80 -v "C:\Development\converter":/var/www/html p3rg3l40/s