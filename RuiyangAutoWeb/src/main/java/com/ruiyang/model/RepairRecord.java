package com.ruiyang.model;

import java.sql.Date;
import java.time.LocalDate;

public class RepairRecord {
    // 維修記錄編號
    private int recordId;
    
    // 訂單編號
    private int orderId;
    
    // 維修日期
    private LocalDate repairDate;
    
    // 維修狀態
    private String repairStatus;
    
    // 維修技師編號
    private int technicianId;

    /**
     * 建構子，用於初始化 RepairRecord 物件
     */
    public RepairRecord(int recordId, int orderId, LocalDate repairDate, String repairStatus, int technicianId) {
        System.out.println("Initializing RepairRecord...");
        this.recordId = recordId;
        this.orderId = orderId;
        this.repairDate = repairDate;
        this.repairStatus = repairStatus;
        this.technicianId = technicianId;
        System.out.println("RepairRecord initialized successfully.");
    }

    // Getter 和 Setter 方法

    public RepairRecord(int int1, int int2, Date date, String string, int int3) {
		// TODO Auto-generated constructor stub
	}

	public int getRecordId() {
        System.out.println("Getting recordId: " + recordId);
        return recordId;
    }

    public void setRecordId(int recordId) {
        System.out.println("Setting recordId to: " + recordId);
        this.recordId = recordId;
    }

    public int getOrderId() {
        System.out.println("Getting orderId: " + orderId);
        return orderId;
    }

    public void setOrderId(int orderId) {
        System.out.println("Setting orderId to: " + orderId);
        this.orderId = orderId;
    }

    public LocalDate getRepairDate() {
        System.out.println("Getting repairDate: " + repairDate);
        return repairDate;
    }

    public void setRepairDate(LocalDate repairDate) {
        System.out.println("Setting repairDate to: " + repairDate);
        this.repairDate = repairDate;
    }

    public String getRepairStatus() {
        System.out.println("Getting repairStatus: " + repairStatus);
        return repairStatus;
    }

    public void setRepairStatus(String repairStatus) {
        System.out.println("Setting repairStatus to: " + repairStatus);
        this.repairStatus = repairStatus;
    }

    public int getTechnicianId() {
        System.out.println("Getting technicianId: " + technicianId);
        return technicianId;
    }

    public void setTechnicianId(int technicianId) {
        System.out.println("Setting technicianId to: " + technicianId);
        this.technicianId = technicianId;
    }

    @Override
    public String toString() {
        String result = "RepairRecord{" +
                "recordId=" + recordId +
                ", orderId=" + orderId +
                ", repairDate=" + repairDate +
                ", repairStatus='" + repairStatus + '\'' +
                ", technicianId=" + technicianId +
                '}';
        System.out.println("toString() method called: " + result);
        return result;
    }
}
