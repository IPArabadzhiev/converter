FROM fauria/lamp

WORKDIR /var/www/html

RUN apt-get update -y && \
    apt-get install cron -y && \
    apt-get install ffmpeg -y && \
    curl -L https://yt-dl.org/downloads/latest/youtube-dl -o /usr/local/bin/youtube-dl && \
    chmod a+rx /usr/local/bin/youtube-dl

COPY . .

