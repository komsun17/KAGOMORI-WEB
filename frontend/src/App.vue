<template>
  <div class="container">
    <h1>Time Sheet Monitor</h1>

    <table class="timesheet-table" v-if="timesheet.length > 0">
      <thead>
        <tr>
          <th>Project</th>
          <th>Prod Order No</th>
          <th>Plan Start</th>
          <th>Plan Finish</th>
          <th>ST.HR</th>
          <th>AT.HR</th>
          <th>Work By</th>
          <th>Start Time</th>
          <th>End Time</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in timesheet" :key="index">
          <td>{{ row.Project }}</td>
          <td>{{ row["Prod_ Order No_"] }}</td>
          <td>{{ row["Plan Start"] }}</td>
          <td>{{ row["Plan Finish"] }}</td>
          <td>{{ row["ST.HR"] }}</td>
          <td>{{ row["AT.HR"] }}</td>
          <td>{{ row["Work By"] }}</td>
          <td>{{ row["Starting Time"] }}</td>
          <td>{{ row["Ending Time"] }}</td>
        </tr>
      </tbody>
    </table>

    <p v-else>Loading data...</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      timesheet: [],
    };
  },
  mounted() {
    fetch("http://localhost/kagomori-web/backend/get_timesheet.php")
      .then((res) => res.json())
      .then((data) => {
        this.timesheet = data;
      })
      .catch((err) => {
        console.error("Fetch error:", err);
      });
  },
};
</script>

<style scoped>
.container {
  padding: 20px;
  font-family: Arial, sans-serif;
}

h1 {
  margin-bottom: 20px;
}

.timesheet-table {
  width: 100%;
  border-collapse: collapse;
}

.timesheet-table th,
.timesheet-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
}

.timesheet-table th {
  background-color: #f2f2f2;
  font-weight: bold;
}
</style>
