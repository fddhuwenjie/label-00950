#!/bin/bash
# 跨境电商平台 - 数据备份脚本
# 用法: ./backup.sh [备份目录]
# 定时任务示例: 0 2 * * * /path/to/backup.sh /data/backups

set -e

BACKUP_DIR="${1:-./backups}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_PATH="${BACKUP_DIR}/${TIMESTAMP}"

mkdir -p "${BACKUP_PATH}"

echo "=== 跨境电商平台数据备份 ==="
echo "备份时间: $(date)"
echo "备份目录: ${BACKUP_PATH}"

# 1. 备份 MySQL 数据库
echo ">>> 备份数据库..."
docker exec ecommerce-mysql mysqldump \
    -u wordpress \
    -pwordpress_password_change_me \
    --single-transaction \
    --routines \
    --triggers \
    wordpress > "${BACKUP_PATH}/database.sql"
echo "    数据库备份完成: database.sql ($(du -h "${BACKUP_PATH}/database.sql" | cut -f1))"

# 2. 备份 WordPress 上传文件
echo ">>> 备份上传文件..."
docker cp ecommerce-backend:/var/www/html/wp-content/uploads "${BACKUP_PATH}/uploads" 2>/dev/null || echo "    无上传文件"
echo "    上传文件备份完成"

# 3. 备份插件配置
echo ">>> 备份插件..."
docker cp ecommerce-backend:/var/www/html/wp-content/plugins/cross-border-commerce "${BACKUP_PATH}/cross-border-commerce"
echo "    插件备份完成"

# 4. 压缩备份
echo ">>> 压缩备份文件..."
cd "${BACKUP_DIR}"
tar -czf "${TIMESTAMP}.tar.gz" "${TIMESTAMP}"
rm -rf "${TIMESTAMP}"
echo "    压缩完成: ${TIMESTAMP}.tar.gz ($(du -h "${TIMESTAMP}.tar.gz" | cut -f1))"

# 5. 清理 30 天前的备份
echo ">>> 清理旧备份..."
find "${BACKUP_DIR}" -name "*.tar.gz" -mtime +30 -delete
echo "    已清理 30 天前的备份"

echo ""
echo "=== 备份完成 ==="
echo "备份文件: ${BACKUP_DIR}/${TIMESTAMP}.tar.gz"
