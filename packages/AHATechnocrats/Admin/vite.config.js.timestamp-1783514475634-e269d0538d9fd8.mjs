// vite.config.js
import { defineConfig, loadEnv } from "file:///E:/AHA%20Rohit/omics-logic-crm/packages/AHATechnocrats/Admin/node_modules/vite/dist/node/index.js";
import vue from "file:///E:/AHA%20Rohit/omics-logic-crm/packages/AHATechnocrats/Admin/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import laravel from "file:///E:/AHA%20Rohit/omics-logic-crm/packages/AHATechnocrats/Admin/node_modules/laravel-vite-plugin/dist/index.js";
import path from "path";
var vite_config_default = defineConfig(({ mode }) => {
  const envDir = "../../../";
  Object.assign(process.env, loadEnv(mode, envDir));
  return {
    build: {
      emptyOutDir: true
    },
    envDir,
    server: {
      host: process.env.VITE_HOST || "localhost",
      port: process.env.VITE_PORT || 5173,
      cors: true
    },
    plugins: [
      vue(),
      laravel({
        hotFile: "../../../public/admin-vite.hot",
        publicDirectory: "../../../public",
        buildDirectory: "admin/build",
        input: [
          "src/Resources/assets/css/app.css",
          "src/Resources/assets/js/app.js",
          "src/Resources/assets/js/chart.js"
        ],
        refresh: true
      })
    ],
    experimental: {
      renderBuiltUrl(filename, { hostId, hostType, type }) {
        if (hostType === "css") {
          return path.basename(filename);
        }
      }
    }
  };
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJFOlxcXFxBSEEgUm9oaXRcXFxcb21pY3MtbG9naWMtY3JtXFxcXHBhY2thZ2VzXFxcXEFIQVRlY2hub2NyYXRzXFxcXEFkbWluXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ZpbGVuYW1lID0gXCJFOlxcXFxBSEEgUm9oaXRcXFxcb21pY3MtbG9naWMtY3JtXFxcXHBhY2thZ2VzXFxcXEFIQVRlY2hub2NyYXRzXFxcXEFkbWluXFxcXHZpdGUuY29uZmlnLmpzXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ltcG9ydF9tZXRhX3VybCA9IFwiZmlsZTovLy9FOi9BSEElMjBSb2hpdC9vbWljcy1sb2dpYy1jcm0vcGFja2FnZXMvQUhBVGVjaG5vY3JhdHMvQWRtaW4vdml0ZS5jb25maWcuanNcIjtpbXBvcnQgeyBkZWZpbmVDb25maWcsIGxvYWRFbnYgfSBmcm9tIFwidml0ZVwiO1xuaW1wb3J0IHZ1ZSBmcm9tIFwiQHZpdGVqcy9wbHVnaW4tdnVlXCI7XG5pbXBvcnQgbGFyYXZlbCBmcm9tIFwibGFyYXZlbC12aXRlLXBsdWdpblwiO1xuaW1wb3J0IHBhdGggZnJvbSBcInBhdGhcIjtcblxuZXhwb3J0IGRlZmF1bHQgZGVmaW5lQ29uZmlnKCh7IG1vZGUgfSkgPT4ge1xuICAgIGNvbnN0IGVudkRpciA9IFwiLi4vLi4vLi4vXCI7XG5cbiAgICBPYmplY3QuYXNzaWduKHByb2Nlc3MuZW52LCBsb2FkRW52KG1vZGUsIGVudkRpcikpO1xuXG4gICAgcmV0dXJuIHtcbiAgICAgICAgYnVpbGQ6IHtcbiAgICAgICAgICAgIGVtcHR5T3V0RGlyOiB0cnVlLFxuICAgICAgICB9LFxuXG4gICAgICAgIGVudkRpcixcblxuICAgICAgICBzZXJ2ZXI6IHtcbiAgICAgICAgICAgIGhvc3Q6IHByb2Nlc3MuZW52LlZJVEVfSE9TVCB8fCBcImxvY2FsaG9zdFwiLFxuICAgICAgICAgICAgcG9ydDogcHJvY2Vzcy5lbnYuVklURV9QT1JUIHx8IDUxNzMsXG4gICAgICAgICAgICBjb3JzOiB0cnVlLFxuICAgICAgICB9LFxuXG4gICAgICAgIHBsdWdpbnM6IFtcbiAgICAgICAgICAgIHZ1ZSgpLFxuXG4gICAgICAgICAgICBsYXJhdmVsKHtcbiAgICAgICAgICAgICAgICBob3RGaWxlOiBcIi4uLy4uLy4uL3B1YmxpYy9hZG1pbi12aXRlLmhvdFwiLFxuICAgICAgICAgICAgICAgIHB1YmxpY0RpcmVjdG9yeTogXCIuLi8uLi8uLi9wdWJsaWNcIixcbiAgICAgICAgICAgICAgICBidWlsZERpcmVjdG9yeTogXCJhZG1pbi9idWlsZFwiLFxuICAgICAgICAgICAgICAgIGlucHV0OiBbXG4gICAgICAgICAgICAgICAgICAgIFwic3JjL1Jlc291cmNlcy9hc3NldHMvY3NzL2FwcC5jc3NcIixcbiAgICAgICAgICAgICAgICAgICAgXCJzcmMvUmVzb3VyY2VzL2Fzc2V0cy9qcy9hcHAuanNcIixcbiAgICAgICAgICAgICAgICAgICAgXCJzcmMvUmVzb3VyY2VzL2Fzc2V0cy9qcy9jaGFydC5qc1wiLFxuICAgICAgICAgICAgICAgIF0sXG4gICAgICAgICAgICAgICAgcmVmcmVzaDogdHJ1ZSxcbiAgICAgICAgICAgIH0pLFxuICAgICAgICBdLFxuXG4gICAgICAgIGV4cGVyaW1lbnRhbDoge1xuICAgICAgICAgICAgcmVuZGVyQnVpbHRVcmwoZmlsZW5hbWUsIHsgaG9zdElkLCBob3N0VHlwZSwgdHlwZSB9KSB7XG4gICAgICAgICAgICAgICAgaWYgKGhvc3RUeXBlID09PSBcImNzc1wiKSB7XG4gICAgICAgICAgICAgICAgICAgIHJldHVybiBwYXRoLmJhc2VuYW1lKGZpbGVuYW1lKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9LFxuICAgICAgICB9LFxuICAgIH07XG59KTtcbiJdLAogICJtYXBwaW5ncyI6ICI7QUFBOFcsU0FBUyxjQUFjLGVBQWU7QUFDcFosT0FBTyxTQUFTO0FBQ2hCLE9BQU8sYUFBYTtBQUNwQixPQUFPLFVBQVU7QUFFakIsSUFBTyxzQkFBUSxhQUFhLENBQUMsRUFBRSxLQUFLLE1BQU07QUFDdEMsUUFBTSxTQUFTO0FBRWYsU0FBTyxPQUFPLFFBQVEsS0FBSyxRQUFRLE1BQU0sTUFBTSxDQUFDO0FBRWhELFNBQU87QUFBQSxJQUNILE9BQU87QUFBQSxNQUNILGFBQWE7QUFBQSxJQUNqQjtBQUFBLElBRUE7QUFBQSxJQUVBLFFBQVE7QUFBQSxNQUNKLE1BQU0sUUFBUSxJQUFJLGFBQWE7QUFBQSxNQUMvQixNQUFNLFFBQVEsSUFBSSxhQUFhO0FBQUEsTUFDL0IsTUFBTTtBQUFBLElBQ1Y7QUFBQSxJQUVBLFNBQVM7QUFBQSxNQUNMLElBQUk7QUFBQSxNQUVKLFFBQVE7QUFBQSxRQUNKLFNBQVM7QUFBQSxRQUNULGlCQUFpQjtBQUFBLFFBQ2pCLGdCQUFnQjtBQUFBLFFBQ2hCLE9BQU87QUFBQSxVQUNIO0FBQUEsVUFDQTtBQUFBLFVBQ0E7QUFBQSxRQUNKO0FBQUEsUUFDQSxTQUFTO0FBQUEsTUFDYixDQUFDO0FBQUEsSUFDTDtBQUFBLElBRUEsY0FBYztBQUFBLE1BQ1YsZUFBZSxVQUFVLEVBQUUsUUFBUSxVQUFVLEtBQUssR0FBRztBQUNqRCxZQUFJLGFBQWEsT0FBTztBQUNwQixpQkFBTyxLQUFLLFNBQVMsUUFBUTtBQUFBLFFBQ2pDO0FBQUEsTUFDSjtBQUFBLElBQ0o7QUFBQSxFQUNKO0FBQ0osQ0FBQzsiLAogICJuYW1lcyI6IFtdCn0K
