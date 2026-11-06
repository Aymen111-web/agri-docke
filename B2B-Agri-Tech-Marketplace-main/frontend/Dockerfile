# Multi-stage Dockerfile for Vue 3 Frontend
# Stage 1: Build the production static assets
FROM node:22-alpine AS builder

WORKDIR /app

# Copy package manifests
COPY package.json package-lock.json ./

# Install dependencies using the existing lockfile
RUN npm ci

# Copy application source files
COPY . .

# Build argument for the API base URL (defaults to /api for reverse proxy architecture)
ARG VITE_API_BASE_URL=/api
ENV VITE_API_BASE_URL=${VITE_API_BASE_URL}

# Build Vue application for production
RUN npm run build

# Stage 2: Serve compiled assets with official Nginx
FROM nginx:alpine

# Remove default Nginx website and configs
RUN rm -rf /usr/share/nginx/html/* /etc/nginx/conf.d/*

# Copy custom Nginx configuration with SPA history fallback
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy built application from builder stage
COPY --from=builder /app/dist /usr/share/nginx/html

# Expose internal HTTP port
EXPOSE 80

# Run Nginx in the foreground
CMD ["nginx", "-g", "daemon off;"]

