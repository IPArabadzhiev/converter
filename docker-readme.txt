docker container run -d -p 80:80 -v $(pwd):/var/www/html --name converter-server fauria/lamp
docker container run -d -p 80:80 -v $(pwd):/var/www/html --name converter-server p3rg3l40/converter-server

-- Cygwin
docker container run -d -p 80:80 -v "C:\Development\converter-server":/var/www/html p3rg3l40/converter-ffmpeg-youtube-dl

-- youtube-dl
    youtube-dl --extract-audio --audio-format mp3 https://www.youtube.com/watch?v=d-o3eB9sfls

    youtube-dl https://www.youtube.com/watch?v=d-o3eB9sfls

    youtube-dl -J https://www.youtube.com/watch?v=d-o3eB9sfls -- get json info of everything about the video
