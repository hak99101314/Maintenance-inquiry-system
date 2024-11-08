package com.ruiyang.model;

import java.sql.Date;

// 訂單類別，表示一個訂單的基本資料
public class Order {
    private int orderId;          // 訂單編號
    private int userId;           // 用戶編號
    private int quotationId;      // 報價編號
    private Date orderDate;  // 訂單日期
    private String status;        // 訂單狀態

    // 建構子，用於初始化一個新的訂單實例
    public Order(int orderId, int userId, int quotationId, Date date, String status) {
        System.out.println("Initializing Order object...");  // 檢查初始化是否成功
        this.orderId = orderId;
        this.userId = userId;
        this.quotationId = quotationId;
        this.orderDate = date;
        this.status = status;
        System.out.println("Order object initialized successfully.");
    }

    // Getter 和 Setter 方法，用於取得和設置訂單的各個屬性

  
	public int getOrderId() {
        System.out.println("Getting order ID: " + orderId);  // 檢查取得 orderId 是否正確
        return orderId;  // 獲取訂單編號
    }

    public void setOrderId(int orderId) {
        System.out.println("Setting order ID to: " + orderId);  // 檢查設置 orderId 是否正確
        this.orderId = orderId;
    }

    public int getUserId() {
        System.out.println("Getting user ID: " + userId);  // 檢查取得 userId 是否正確
        return userId;  // 獲取用戶編號
    }

    public void setUserId(int userId) {
        System.out.println("Setting user ID to: " + userId);  // 檢查設置 userId 是否正確
        this.userId = userId;
    }

    public int getQuotationId() {
        System.out.println("Getting quotation ID: " + quotationId);  // 檢查取得 quotationId 是否正確
        return quotationId;  // 獲取報價編號
    }

    public void setQuotationId(int quotationId) {
        System.out.println("Setting quotation ID to: " + quotationId);  // 檢查設置 quotationId 是否正確
        this.quotationId = quotationId;
    }

    public Date getOrderDate() {
        System.out.println("Getting order date: " + orderDate);  // 檢查取得 orderDate 是否正確
        return orderDate;  // 獲取訂單日期
    }

    public void setOrderDate(Date orderDate) {
        System.out.println("Setting order date to: " + orderDate);  // 檢查設置 orderDate 是否正確
        this.orderDate = orderDate;
    }

    public String getStatus() {
        System.out.println("Getting order status: " + status);  // 檢查取得 status 是否正確
        return status;  // 獲取訂單狀態
    }

    public void setStatus(String status) {
        System.out.println("Setting order status to: " + status);  // 檢查設置 status 是否正確
        this.status = status;
    }

    // toString 方法，回傳訂單物件的字串表示，便於檢視訂單內容
    @Override
    public String toString() {
        System.out.println("Converting Order object to string...");  // 檢查 toString 是否執行
        return "Order{" +
                "orderId=" + orderId +
                ", userId=" + userId +
                ", quotationId=" + quotationId +
                ", orderDate=" + orderDate +
                ", status='" + status + '\'' +
                '}';
    }
}
