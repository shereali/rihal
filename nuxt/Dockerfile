# Nuxt 4 frontend — Node 22
FROM node:22-alpine

WORKDIR /app

# Install dependencies first (better layer caching)
COPY package.json package-lock.json* ./
RUN npm install

# Copy application source
COPY . .

# Build for production
RUN npm run build

ENV HOST=0.0.0.0
ENV PORT=3000
ENV NUXT_PUBLIC_API_BASE=http://localhost:8000/api/v1

EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]
