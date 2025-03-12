// -------------------------------------------------------------------------------------------------------------------------------------------
// Dashboard 2 : Chart Init Js
// -------------------------------------------------------------------------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
  // -----------------------------------------------------------------------
  // Sales overview
  // -----------------------------------------------------------------------

  var options_Sales_Overview = {
    series: [
      {
        name: "Total Sales",
        data: [30, 150, 110, 240, 180, 100, 150],
      },
      {
        name: "This Month",
        data: [100, 50, 130, 70, 135, 80, 240],
      },
    ],
    chart: {
      height: 350,
      type: "area",
      foreColor: "#adb0bb",
      fontFamily: "inherit",
      zoom: {
        enabled: false,
      },
      toolbar: {
        show: false,
      },
    },
    colors: ["#0acc95", "#398bf7"],
    dataLabels: {
      enabled: false,
    },
    stroke: {
      curve: "smooth",
      width: 2,
    },
    markers: {
      size: 3,
      strokeColors: "transparent",
    },
    grid: {
      show: true,
      borderColor: "rgba(0,0,0,.1)",
      xaxis: {
        lines: {
          show: false,
        },
      },
      yaxis: {
        lines: {
          show: true,
        },
      },
    },
    xaxis: {
      type: "category",
      categories: [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
      ],
      tickAmount: "16",
      tickPlacement: "on",
      axisTicks: {
        show: true,
        borderType: "solid",
        color: "rgba(0,0,0,0.5)",
        height: 6,
        offsetX: 0,
        offsetY: 0,
      },
      axisBorder: {
        show: false
      },
    },
    legend: {
      show: false,
    },
    tooltip: {
      theme: "dark",
    },
  };

  var chart_line_overview = new ApexCharts(
    document.querySelector("#Sales-Overview"),
    options_Sales_Overview
  );
  chart_line_overview.render();

  // -----------------------------------------------------------------------
  // Visitor
  // -----------------------------------------------------------------------

  var option_Visit_Separation = {
    series: [50, 40, 30, 10],
    labels: ["Mobile", "Tablet", "Other", "Desktop"],
    chart: {
      type: "donut",
      fontFamily: "inherit",
      height: 225,
      offsetY: 30,
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      width: 0,
    },
    plotOptions: {
      pie: {
        expandOnClick: true,
        donut: {
          size: "80",
          labels: {
            show: true,
            name: {
              show: true,
              offsetY: 10,
            },
            value: {
              show: false,
            },
            total: {
              show: true,
              color: "#000",
              fontFamily: "inherit",
              fontSize: "26px",
              fontWeight: 600,
              label: "Visits",
            },
          },
        },
      },
    },
    colors: ["var(--bs-primary)", "#26c6da", "#eceff1", "var(--bs-info)"],
    tooltip: {
      show: true,
      fillSeriesColor: false,
    },
    legend: {
      show: false,
    },
    responsive: [
      {
        breakpoint: 1025,
        options: {
          chart: {
            height: 220,
            width: 220,
          },
        },
      },
      {
        breakpoint: 769,
        options: {
          chart: {
            height: 250,
            width: 250,
          },
        },
      },
    ],
  };

  var chart_pie_donut_status = new ApexCharts(
    document.querySelector("#Visit-Separation"),
    option_Visit_Separation
  );
  chart_pie_donut_status.render();

  // -----------------------------------------------------------------------
  // Website Visitor
  // -----------------------------------------------------------------------

  var option_Website_Visit = {
    series: [
      {
        name: "Series A View ",
        data: [1.5, 2.7, 2.2, 3.6, 1.5, 1.0, 2.3, 1.5, 1.0, 2.3],
      },
      {
        name: "Series B View ",
        data: [-1.8, -1.1, -2.5, -1.5, -0.6, -1.8, -1.2, -0.6, -1.8, -1.2],
      },
    ],
    chart: {
      fontFamily: "inherit",
      height: 360,
      foreColor: "#a1aab2",
      type: "bar",
      toolbar: {
        show: false,
      },
      stacked: true,
    },
    colors: ["var(--bs-success)", "var(--bs-primary)"],
    plotOptions: {
      bar: {
        horizontal: false,
        barHeight: "60%",
        columnWidth: "20%",
        borderRadius: [6],
        borderRadiusApplication: "end",
        borderRadiusWhenStacked: "all",
      },
    },
    dataLabels: {
      enabled: false,
    },
    legend: {
      show: false,
    },
    grid: {
      borderColor: "rgba(0,0,0,0.1)",
      strokeDashArray: 3,
      xaxis: {
        lines: {
          show: false,
        },
      },
    },
    yaxis: {
      min: -5,
      max: 5,
      title: {
        
      },
    },
    xaxis: {
      axisBorder: {
        show: false,
      },
      categories: [
        "16/08",
        "17/08",
        "18/08",
        "19/08",
        "20/08",
        "21/08",
        "22/08",
        "23/08",
        "24/08",
        "25/08",
      ],
    },
    yaxis: {
      tickAmount: 4,
    },
    tooltip: {
      x: {
        format: "dd/MM/yy HH:mm",
      },
      theme: "dark",
    },
    legend: {
      show: false,
    },
  };

  var chart_area_spline = new ApexCharts(
    document.querySelector("#Website-Visit"),
    option_Website_Visit
  );
  chart_area_spline.render();
});
