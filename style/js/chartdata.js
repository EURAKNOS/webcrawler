// JavaScript Document

// DOUGHNUT 1
//	new Chart(document.getElementById("overall-01"),{
//		type:"doughnut",
//		data:{
//			datasets:[{
//				datalabels: {
//					display: false,
//				},
//				data:[63,37],
//				backgroundColor:[
//					"#ff6b6c","#e5e5e5"
//				],
//				borderWidth: 0,
//			}],
//		},
//
//		options: {
//			hover: {mode: null},
//			rotation: 1 * Math.PI,
//			circumference: 1 * Math.PI,
//			legend: {
//				display: false,
//			},
//			title: {
//				display: false,
//			},
//			tooltips: {
//				enabled: false,
//			}
//		},
//	});

// DOUGHNUT 2
//	new Chart(document.getElementById("overall-02"),{
//		type:"doughnut",
//		data:{
//			datasets:[{
//				datalabels: {
//					display: false,
//				},
//				data:[42,58],
//				backgroundColor:[
//					"#7aead3","#e5e5e5"
//				],
//				borderWidth: 0,
//			}],
//		},
//
//		options: {
//			hover: {mode: null},
//			rotation: 1 * Math.PI,
//			circumference: 1 * Math.PI,
//			legend: {
//				display: false,
//			},
//			title: {
//				display: false,
//			},
//			tooltips: {
//				enabled: false,
//			}
//		},
//	});

// STAT TEXT
$(document).ready(function() {
	var textPercentage = $("#stat-text").data("percentage"),
	prezPercentage = $("#stat-prez").data("percentage"),
	imagePercentage = $("#stat-image").data("percentage"),
	videoPercentage = $("#stat-video").data("percentage"),
	otherPercentage = $("#stat-other").data("percentage");

	new Chart(document.getElementById("stat-text"),{
		plugins: [ChartDataLabels],
		type:'horizontalBar',
		data:{
			datasets:[{
				data:[textPercentage],
				backgroundColor: '#7ad7ea',
				barPercentage: 1,
				categoryPercentage: 1,
				datalabels: {
					color: '#0b1c26',
					labels: {
						title: {
							font: {
								family: 'Lato',
							}
						},
					},
					formatter: function(value, context) {
						return value + '%';
					},
				},
			}],
		},

		options: {
			animation: false,
			legend: {
				display: false,
			},
			title: {
				display: false,
			},
			scales: {
				yAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,	// Y tengely feliratok elrejtése
					},
				}],
				xAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,
						min:0,
						max:100,
					},
				}],
			},
			tooltips: {
				enabled: false,
			},
		},
	});

// STAT PRESENTATION
	new Chart(document.getElementById("stat-prez"),{
		plugins: [ChartDataLabels],
		type:'horizontalBar',
		data:{
			datasets:[{
				data:[prezPercentage],
				backgroundColor: '#ffda65',
				barPercentage: 1,
				categoryPercentage: 1,
				datalabels: {
					color: '#0b1c26',
					labels: {
						title: {
							font: {
								family: 'Lato',
							}
						},
					},
					formatter: function(value, context) {
						return value + '%';
					},
				},
			}],
		},

		options: {
			animation: false,
			legend: {
				display: false,
			},
			title: {
				display: false,
			},
			scales: {
				yAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,	// Y tengely feliratok elrejtése
					},
				}],
				xAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,
						min:0,
						max:100,
					},
				}],
			},
			tooltips: {
				enabled: false,
			},
		},
	});

// STAT IMAGE
	new Chart(document.getElementById("stat-image"),{
		plugins: [ChartDataLabels],
		type:'horizontalBar',
		data:{
			datasets:[{
				data:[imagePercentage],
				backgroundColor: '#7884f7',
				barPercentage: 1,
				categoryPercentage: 1,
				datalabels: {
					color: '#0b1c26',
					labels: {
						title: {
							font: {
								family: 'Lato',
							}
						},
					},
					formatter: function(value, context) {
						return value + '%';
					},
				},
			}],
		},

		options: {
			animation: false,
			legend: {
				display: false,
			},
			title: {
				display: false,
			},
			scales: {
				yAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,	// Y tengely feliratok elrejtése
					},
				}],
				xAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,
						min:0,
						max:100,
					},
				}],
			},
			tooltips: {
				enabled: false,
			},
		},
	});

// STAT VIDEO
	new Chart(document.getElementById("stat-video"),{
		plugins: [ChartDataLabels],
		type:'horizontalBar',
		data:{
			datasets:[{
				data:[videoPercentage],
				backgroundColor: '#ff9165',
				barPercentage: 1,
				categoryPercentage: 1,
				datalabels: {
					color: '#0b1c26',
					labels: {
						title: {
							font: {
								family: 'Lato',
							}
						},
					},
					formatter: function(value, context) {
						return value + '%';
					},
				},
			}],
		},

		options: {
			animation: false,
			legend: {
				display: false,
			},
			title: {
				display: false,
			},
			scales: {
				yAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,	// Y tengely feliratok elrejtése
					},
				}],
				xAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,
						min:0,
						max:100,
					},
				}],
			},
			tooltips: {
				enabled: false,
			},
		},
	});
	// STAT AUDIO
	new Chart(document.getElementById("stat-audio"),{
		plugins: [ChartDataLabels],
		type:'horizontalBar',
		data:{
			datasets:[{
				data:[videoPercentage],
				backgroundColor: '#ff9165',
				barPercentage: 1,
				categoryPercentage: 1,
				datalabels: {
					color: '#0b1c26',
					labels: {
						title: {
							font: {
								family: 'Lato',
							}
						},
					},
					formatter: function(value, context) {
						return value + '%';
					},
				},
			}],
		},

		options: {
			animation: false,
			legend: {
				display: false,
			},
			title: {
				display: false,
			},
			scales: {
				yAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,	// Y tengely feliratok elrejtése
					},
				}],
				xAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,
						min:0,
						max:100,
					},
				}],
			},
			tooltips: {
				enabled: false,
			},
		},
	});
	
// STAT OTHER
	new Chart(document.getElementById("stat-other"),{
		plugins: [ChartDataLabels],
		type:'horizontalBar',
		data:{
			datasets:[{
				data:[otherPercentage],
				backgroundColor: '#82fc68',
				barPercentage: 1,
				categoryPercentage: 1,
				datalabels: {
					color: '#0b1c26',
					labels: {
						title: {
							font: {
								family: 'Lato',
							}
						},
					},
					formatter: function(value, context) {
						return value + '%';
					},
				},
			}],
		},

		options: {
			animation: false,
			legend: {
				display: false,
			},
			title: {
				display: false,
			},
			scales: {
				yAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,	// Y tengely feliratok elrejtése
					},
				}],
				xAxes: [{
					display: false,
					stacked: true,
					ticks: {
						display: false,
						min:0,
						max:100,
					},
				}],
			},
			tooltips: {
				enabled: false,
			},
		},
	});
});

// STAT TEXT


// STAT TEXT


// STAT TEXT

