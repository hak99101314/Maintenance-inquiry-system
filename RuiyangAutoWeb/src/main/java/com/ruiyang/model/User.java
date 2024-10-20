package com.ruiyang.model;

import java.time.LocalDate;

public class User {
    private int userId;               // 用戶編號
    private String username;          // 用戶名
    private String passwordHash;      // 密碼哈希
    private String role;              // 角色（例如：customer, staff, admin, manager）
    private String fullName;          // 全名
    private String contactNumber;     // 聯繫電話
    private String address;           // 地址
    private String email;             // 電子郵件
    private LocalDate createdDate;    // 用戶創建日期
    private boolean isEnabled;        // 用戶是否啟用

    // 建構子
    public User(int userId, String username, String passwordHash, String role, String fullName,
                String contactNumber, String address, String email, LocalDate createdDate, boolean isEnabled) {
        this.userId = userId;
        this.username = username;
        this.passwordHash = passwordHash;
        this.role = role;
        this.fullName = fullName;
        this.contactNumber = contactNumber;
        this.address = address;
        this.email = email;
        this.createdDate = createdDate;
        this.isEnabled = isEnabled;
    }

    // Getter 和 Setter 方法

    public int getUserId() {
        return userId;
    }

    public void setUserId(int userId) {
        this.userId = userId;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public String getPasswordHash() {
        return passwordHash;
    }

    public void setPasswordHash(String passwordHash) {
        this.passwordHash = passwordHash;
    }

    public String getRole() {
        return role;
    }

    public void setRole(String role) {
        this.role = role;
    }

    public String getFullName() {
        return fullName;
    }

    public void setFullName(String fullName) {
        this.fullName = fullName;
    }

    public String getContactNumber() {
        return contactNumber;
    }

    public void setContactNumber(String contactNumber) {
        this.contactNumber = contactNumber;
    }

    public String getAddress() {
        return address;
    }

    public void setAddress(String address) {
        this.address = address;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public LocalDate getCreatedDate() {
        return createdDate;
    }

    public void setCreatedDate(LocalDate createdDate) {
        this.createdDate = createdDate;
    }

    public boolean isEnabled() {
        return isEnabled;
    }

    public void setEnabled(boolean enabled) {
        isEnabled = enabled;
    }

    @Override
    public String toString() {
        return "User{" +
                "userId=" + userId +
                ", username='" + username + '\'' +
                ", role='" + role + '\'' +
                ", fullName='" + fullName + '\'' +
                ", contactNumber='" + contactNumber + '\'' +
                ", address='" + address + '\'' +
                ", email='" + email + '\'' +
                ", createdDate=" + createdDate +
                ", isEnabled=" + isEnabled +
                '}';
    }
}
