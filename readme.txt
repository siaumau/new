 如何啟動專案：

  後端 (Laravel):


   1. 打開命令提示字元 (CMD) 或 PowerShell。
   2. 進入後端專案目錄：

   1     cd D:\sideproject\new\backend

   3. 啟動 Laravel 開發伺服器：

   1     php artisan serve

      這將會在 http://127.0.0.1:8000 啟動伺服器 (或顯示其他端口)。

   4. 您可以透過瀏覽器訪問 http://localhost:8000/documentation 來查看 Swagger API 文件。

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


我將建立 ItemController。我會使用 php artisan make:controller ItemController --api 命令，這會生成一個包含基本 API 方法的控制器。

  一旦後端可以被外部訪問，前端的 axios 請求 URL 也需要從 http://127.0.0.1:8000 更改為後端的實際 IP 地址或網域名稱。


  由於我已經將前端程式碼中的所有 localhost:8000 替換為 127.0.0.1:8000，如果您使用 php artisan serve --host=0.0.0.0 --port=8000 啟動後端，前端應該可以直接工作。

  如果您希望使用其他 IP 地址或網域名稱，您需要手動修改前端程式碼中的所有 http://127.0.0.1:8000 為您新的後端 URL。


  總結：


   1. 啟動後端： 使用 php artisan serve --host=0.0.0.0 --port=8000 (或您的實際 IP) 啟動 Laravel 伺服器。
   2. 啟動前端： 執行 npm run dev 啟動 Vue 應用程式。
