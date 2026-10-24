@echo off
chcp 65001 >nul
title Day code len GitHub
cd /d "d:\wamp\www\ai-eufy"
echo ========================================================
echo   Đang đẩy toàn bộ mã nguồn lên GitHub...
echo ========================================================
echo.
echo Nếu xuất hiện cửa sổ trình duyệt hỏi đăng nhập GitHub,
echo anh chỉ cần bấm "Sign in with your browser" để xác thực.
echo.

git branch -M main
git push -u origin main

echo.
echo ========================================================
echo Hoàn tất! Nhấn phím bất kỳ để đóng cửa sổ.
pause
