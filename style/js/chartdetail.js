// JavaScript Document

// DOUGHNUT
		new Chart(document.getElementById('chartjs-4'),{
			type:'doughnut',
			data:{
				labels:['PAGE','PDF','JPG','PNG','SWF','DOCX','XLSX','PPTX','EPUB','YOUTUBE','VIMEO','GOOGLE MAPS','1','2','3','4',],
				datasets:[{
					data:[534,180,533,116,70,30,45,230,113,17,33,23,30,30,30,30],
					backgroundColor:[
						// Colors 13 (16)
						'#ff6b6c','#ff9165','#ffb165','#ffc365','#ffda65','#f4f170','#dafd67','#82fc68','#74f0ac','#7aead3','#7ad7ea','#7ac0ea','#7aa5ea','#7884f7','#785df7','#9a6cf8',
					],
					borderWidth: 0,
				}],
			},
			
			options: {
				legend: {
					position: 'right',
					onClick: function(event, legendItem) {
					}
		        },
				title: {
            		display: true,
            		text: 'QUANTITY',
					fontFamily: 'Lato',
					fontSize: 14,
					fontColor: '#0b1c26',
       			},
		    },
		});



// HORIZONTAL BAR

new Chart(document.getElementById('chartjs-2'),{
//	plugins: [ChartDataLabels],
	type:'horizontalBar',
	data:{
		labels:['PAGE','PDF','JPG','PNG','SWF','DOCX','XLSX','PPTX','EPUB','YOUTUBE','VIMEO','GOOGLE MAPS','1','2','3','4',],
		datasets:[{
			data:[70,53,33,11,7,40,45,23,100,17,33,23,30,30,30,30],
			backgroundColor:[
				'#ff6b6c','#ff9165','#ffb165','#ffc365','#ffda65','#f4f170','#dafd67','#82fc68','#74f0ac','#7aead3','#7ad7ea','#7ac0ea','#7aa5ea','#7884f7','#785df7','#9a6cf8',
			],
		}],
	},
			
	options: {
		legend: {
			display: false,
		},
		title: {
			display: true,
			text: 'METADATA AVAILABILITY',
			fontFamily: 'Lato',
			fontSize: 14,
			fontColor: '#0b1c26',
		},
		scales: {
			yAxes: [{
				gridLines: {
					display: false,
				},
				ticks: {
					display: false,	// Y tengely feliratok elrejtése
					fontFamily: 'Lato',
				},
			}],
			xAxes: [{
				ticks: {
					fontFamily: 'Lato',
					callback: function(value, index, values) {
						return value+'%';
					},
				},
			}],
		},
		tooltips: {
			callback: {
				label: function(tooltipItem, data) {
						return data + '%';
					},
			},
		},
	},
});
