package com.ruiyang.model;

import java.time.LocalDate;

public class Inquiry {
    private int inquiryId;
    private int memberId;
    private int customerId;
    private int employeeId;
    private int rating;
    private String feedback;
    private LocalDate inquiryDate; // 使用 LocalDate 來表示日期

    // 詢價單的基本屬性
    private String productName;
    private int quantity;
    private String customerContact;
    private int userId; // 修改為 int 類型
    private String status;

    // 建構子
    public Inquiry(int inquiryId, int memberId, int customerId, int employeeId, int rating, String feedback,
                   LocalDate inquiryDate) {
        this.inquiryId = inquiryId;
        this.memberId = memberId;
        this.customerId = customerId;
        this.employeeId = employeeId;
        this.rating = rating;
        this.feedback = feedback;
        this.inquiryDate = inquiryDate;
    }

    // 詢價單的建構子（供詢價使用）
    public Inquiry(int inquiryId, int userId, String productName, int quantity, LocalDate inquiryDate, String status) {
        this.inquiryId = inquiryId;
        this.userId = userId;
        this.productName = productName;
        this.quantity = quantity;
        this.inquiryDate = inquiryDate;
        this.status = status;
    }

    // Getter 和 Setter 方法

    public int getInquiryId() {
        return inquiryId;
    }

    public void setInquiryId(int inquiryId) {
        this.inquiryId = inquiryId;
    }

    public int getMemberId() {
        return memberId;
    }

    public void setMemberId(int memberId) {
        this.memberId = memberId;
    }

    public int getCustomerId() {
        return customerId;
    }

    public void setCustomerId(int customerId) {
        this.customerId = customerId;
    }

    public int getEmployeeId() {
        return employeeId;
    }

    public void setEmployeeId(int employeeId) {
        this.employeeId = employeeId;
    }

    public int getRating() {
        return rating;
    }

    public void setRating(int rating) {
        this.rating = rating;
    }

    public String getFeedback() {
        return feedback;
    }

    public void setFeedback(String feedback) {
        this.feedback = feedback;
    }

    public LocalDate getInquiryDate() {
        return inquiryDate;
    }

    public void setInquiryDate(LocalDate inquiryDate) {
        this.inquiryDate = inquiryDate;
    }

    public String getProductName() {
        return productName;
    }

    public void setProductName(String productName) {
        this.productName = productName;
    }

    public int getQuantity() {
        return quantity;
    }

    public void setQuantity(int quantity) {
        this.quantity = quantity;
    }

    public String getCustomerContact() {
        return customerContact;
    }

    public void setCustomerContact(String customerContact) {
        this.customerContact = customerContact;
    }

    public int getUserId() {
        return userId;
    }

    public void setUserId(int userId) {
        this.userId = userId;
    }

    public String getStatus() {
        return status;
    }

    public void setStatus(String status) {
        this.status = status;
    }
}
