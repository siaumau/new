 如何啟動專案：

  後端 (Laravel):


   1. 打開命令提示字元 (CMD) 或 PowerShell。
   2. 進入後端專案目錄：

   1     cd D:\sideproject\new\backend

   3. 啟動 Laravel 開發伺服器：

   1     php artisan serve

      這將會在 http://127.0.0.1:8000 啟動伺服器 (或顯示其他端口)。

   4. 您可以透過瀏覽器訪問 http://127.0.0.1:8000/api/documentation 來查看 Swagger API 文件。

  前端 (Vue):


   1. 打開另一個命令提示字元 (CMD) 或 PowerShell。
   2. 進入前端專案目錄：

   1     cd D:\sideproject\new\frontend

   3. 安裝前端依賴：

   1     npm install

   4. 啟動 Vue 開發伺服器：

   1     npm run dev

      這將會在 http://localhost:5173 (或顯示其他端口) 啟動開發伺服器。


  後端 (PHP + Laravel):
   * 在 D:\sideproject\new\backend 建立了 Laravel 專案。
   * 配置了資料庫連接到 inventory_system。
   * 使用 kitloong/laravel-migrations-generator 將您現有的資料庫結構反向工程為 Laravel 遷移檔案，並確保它們被 Laravel 遷移系統識別。
   * 安裝並配置了 l5-swagger，現在可以生成 API 文件。

  前端 (Vue + Tailwind CSS):
   * 在 D:\sideproject\new\frontend 建立了 Vue 專案。
   * 配置了 Tailwind CSS。