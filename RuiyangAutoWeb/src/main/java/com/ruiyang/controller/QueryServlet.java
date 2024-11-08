package com.ruiyang.controller;

import java.io.IOException;
import java.io.PrintWriter;
import java.util.List;

import com.ruiyang.dao.RepairRecordDao;
import com.ruiyang.model.RepairRecord;
import com.google.gson.Gson;

import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

@jakarta.servlet.annotation.WebServlet("/query")
public class QueryServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    // 處理 POST 請求的方法
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        System.out.println("開始處理 POST 請求");

        // 從請求中取得車牌號碼和電話號碼參數
        String plateNumber = request.getParameter("plate_number");
        String phoneNumber = request.getParameter("phone_number");
        System.out.println("取得參數: 車牌號碼=" + plateNumber + ", 電話號碼=" + phoneNumber);

        // 創建 RepairRecordDao 物件，用於操作維修紀錄資料
        RepairRecordDao repairRecordDao = new RepairRecordDao();
        System.out.println("RepairRecordDao 已創建");

        // 根據車牌號碼和電話號碼查詢維修紀錄，並存入列表
		List<RepairRecord> repairRecords = repairRecordDao.getRepairRecords(plateNumber, phoneNumber);
		System.out.println("查詢維修紀錄成功: 共查到 " + repairRecords.size() + " 筆紀錄");

		// 使用 Gson 將查詢結果轉換為 JSON 格式的字串
		Gson gson = new Gson();
		String json = gson.toJson(repairRecords);
		System.out.println("轉換為 JSON 格式成功");

		// 設置回應的內容類型為 JSON 和字符編碼為 UTF-8
		response.setContentType("application/json");
		response.setCharacterEncoding("UTF-8");
		System.out.println("設置回應的內容類型和字符編碼完成");

		// 將 JSON 字串寫入回應輸出
		PrintWriter out = response.getWriter();
		out.print(json);
		out.flush(); // 清空緩衝區，確保資料已經完全輸出
		System.out.println("JSON 輸出成功");

        System.out.println("POST 請求處理結束");
    }
}
