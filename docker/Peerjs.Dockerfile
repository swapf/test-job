FROM node:12

# создание директории приложения
WORKDIR /usr/src/peerjs

# установка зависимостей
# символ астериск ("*") используется для того чтобы по возможности
# скопировать оба файла: package.json и package-lock.json
#COPY package*.json ./

RUN npm install -g peer
# Если вы создаете сборку для продакшн
# RUN npm ci --only=production

#RUN npm install -g peer

# копируем исходный код
#COPY . .

#CMD ["peerjs", "--port 3001"]

EXPOSE 3001

#CMD ["peerjs", "--port 3001"]