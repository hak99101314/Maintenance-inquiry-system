package com.ruiyang.model;

import java.time.LocalDate;

public class Order {
    private int orderId;          // 訂單編號
    private int userId;           // 用戶編號
    private int quotationId;      // 報價編號
    private LocalDate orderDate;  // 訂單日期
    private String status;        // 訂單狀態

    // 建構子
    public Order(int orderId, int userId, int quotationId, LocalDate orderDate, String status) {
        this.orderId = orderId;
        this.userId = userId;
        this.quotationId = quotationId;
        this.orderDate = orderDate;
        this.status = status;
    }

    // Getter 和 Setter 方法

    public int getOrderId() {
        return orderId;
    }

    public void setOrderId(int orderId) {
        this.orderId = orderId;
    }

    public int getUserId() {
        return userId;
    }

    public void setUserId(int userId) {
        this.userId = userId;
    }

    public int getQuotationId() {
        return quotationId;
    }

    public void setQuotationId(int quotationId) {
        this.quotationId = quotationId;
    }

    public LocalDate getOrderDate() {
        return orderDate;
    }

    public void setOrderDate(LocalDate orderDate) {
        this.orderDate = orderDate;
    }

    public String getStatus() {
        return status;
    }

    public void setStatus(String status) {
        this.status = status;
    }

    @Override
    public String toString() {
        return "Order{" +
                "orderId=" + orderId +
                ", userId=" + userId +
                ", quotationId=" + quotationId +
                ", orderDate=" + orderDate +
                ", status='" + status + '\'' +
                '}';
    }
}
