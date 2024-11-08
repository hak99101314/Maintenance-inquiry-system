package com.ruiyang.model;

import java.sql.Date;
import java.time.LocalDate;

public class User {
    private int userId;
    private String username;
    private String passwordHash;
    private String role;
    private String fullName;
    private String contactNumber;
    private String address;
    private String email;
    private LocalDate createdDate;
    private boolean isEnabled;

    // 建構子，初始化每個屬性並列印初始化狀態
    public User(int userId, String username, String passwordHash, String role, String fullName,
                String contactNumber, String address, String email, LocalDate createdDate, boolean isEnabled) {
        this.userId = userId;
        System.out.println("UserId initialized successfully: " + userId);
        
        this.username = username;
        System.out.println("Username initialized successfully: " + username);
        
        this.passwordHash = passwordHash;
        System.out.println("PasswordHash initialized successfully.");

        this.role = role;
        System.out.println("Role initialized successfully: " + role);
        
        this.fullName = fullName;
        System.out.println("FullName initialized successfully: " + fullName);
        
        this.contactNumber = contactNumber;
        System.out.println("ContactNumber initialized successfully: " + contactNumber);
        
        this.address = address;
        System.out.println("Address initialized successfully: " + address);
        
        this.email = email;
        System.out.println("Email initialized successfully: " + email);
        
        this.createdDate = createdDate;
        System.out.println("CreatedDate initialized successfully: " + createdDate);
        
        this.isEnabled = isEnabled;
        System.out.println("IsEnabled initialized successfully: " + isEnabled);
    }

    // Getter 和 Setter 方法，帶有列印訊息以顯示每次設置的狀態

    public User(int int1, String string, String string2, String string3, String string4, String string5, String string6,
			String string7, Date date, boolean boolean1) {
		// TODO Auto-generated constructor stub
	}

	public int getUserId() {
        return userId;
    }

    public void setUserId(int userId) {
        this.userId = userId;
        System.out.println("UserId set successfully: " + userId);
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
        System.out.println("Username set successfully: " + username);
    }

    public String getPasswordHash() {
        return passwordHash;
    }

    public void setPasswordHash(String passwordHash) {
        this.passwordHash = passwordHash;
        System.out.println("PasswordHash set successfully.");
    }

    public String getRole() {
        return role;
    }

    public void setRole(String role) {
        this.role = role;
        System.out.println("Role set successfully: " + role);
    }

    public String getFullName() {
        return fullName;
    }

    public void setFullName(String fullName) {
        this.fullName = fullName;
        System.out.println("FullName set successfully: " + fullName);
    }

    public String getContactNumber() {
        return contactNumber;
    }

    public void setContactNumber(String contactNumber) {
        this.contactNumber = contactNumber;
        System.out.println("ContactNumber set successfully: " + contactNumber);
    }

    public String getAddress() {
        return address;
    }

    public void setAddress(String address) {
        this.address = address;
        System.out.println("Address set successfully: " + address);
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
        System.out.println("Email set successfully: " + email);
    }

    public LocalDate getCreatedDate() {
        return createdDate;
    }

    public void setCreatedDate(LocalDate createdDate) {
        this.createdDate = createdDate;
        System.out.println("CreatedDate set successfully: " + createdDate);
    }

    public boolean isEnabled() {
        return isEnabled;
    }

    public void setEnabled(boolean enabled) {
        isEnabled = enabled;
        System.out.println("IsEnabled set successfully: " + isEnabled);
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

	public Object getPassword() {
		// TODO Auto-generated method stub
		return null;
	}
}
