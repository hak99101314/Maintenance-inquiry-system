package com.ruiyang.model;

import java.time.LocalDate;

public class RepairRecord {
    private int recordId;           // 維修記錄編號
    private int orderId;            // 訂單編號
    private LocalDate repairDate;   // 維修日期
    private String repairStatus;    // 維修狀態
    private int technicianId;       // 維修技師編號

    // 建構子
    public RepairRecord(int recordId, int orderId, LocalDate repairDate, String repairStatus, int technicianId) {
        this.recordId = recordId;
        this.orderId = orderId;
        this.repairDate = repairDate;
        this.repairStatus = repairStatus;
        this.technicianId = technicianId;
    }

    // Getter 和 Setter 方法

    public int getRecordId() {
        return recordId;
    }

    public void setRecordId(int recordId) {
        this.recordId = recordId;
    }

    public int getOrderId() {
        return orderId;
    }

    public void setOrderId(int orderId) {
        this.orderId = orderId;
    }

    public LocalDate getRepairDate() {
        return repairDate;
    }

    public void setRepairDate(LocalDate repairDate) {
        this.repairDate = repairDate;
    }

    public String getRepairStatus() {
        return repairStatus;
    }

    public void setRepairStatus(String repairStatus) {
        this.repairStatus = repairStatus;
    }

    public int getTechnicianId() {
        return technicianId;
    }

    public void setTechnicianId(int technicianId) {
        this.technicianId = technicianId;
    }

    @Override
    public String toString() {
        return "RepairRecord{" +
                "recordId=" + recordId +
                ", orderId=" + orderId +
                ", repairDate=" + repairDate +
                ", repairStatus='" + repairStatus + '\'' +
                ", technicianId=" + technicianId +
                '}';
    }
}
