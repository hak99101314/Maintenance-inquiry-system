package com.ruiyang.controller;

import com.google.gson.Gson;
import com.ruiyang.dao.RepairRecordDao;
import com.ruiyang.model.RepairRecord;
import com.ruiyang.util.DBUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.io.PrintWriter;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.List;

@WebServlet("/query")
public class QueryServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        String plateNumber = request.getParameter("plate_number");
        String phoneNumber = request.getParameter("phone_number");

        try (Connection conn = DBUtil.getConnection()) {
            RepairRecordDao dao = new RepairRecordDao(conn);
            // Retrieve repair records based on plate number and phone number
            List<RepairRecord> repairRecords = dao.getRepairRecords(plateNumber, phoneNumber);

            Gson gson = new Gson();
            String json = gson.toJson(repairRecords);

            response.setContentType("application/json");
            response.setCharacterEncoding("UTF-8");

            PrintWriter out = response.getWriter();
            out.print(json);
            out.flush();
        } catch (SQLException e) {
            e.printStackTrace();
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Database query failed");
        }
    }
}
