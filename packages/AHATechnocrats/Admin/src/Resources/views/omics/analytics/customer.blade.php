<x-admin::layouts>
    <x-slot:title>
        Customer Analytics
    </x-slot>

    <div class="omics-customer-analytics flex flex-col gap-4" v-pre>
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between gap-4 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 max-sm:flex-wrap">
            <div>
                <x-admin::breadcrumbs name="omics.customer-analytics" />

                <h1 class="text-xl font-bold dark:text-white">
                    Customer Analytics
                </h1>

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Acquisition, demographics and satisfaction for converted customers
                    <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                        · Last synced {{ $data['lastSynced'] ? \Carbon\Carbon::parse($data['lastSynced'])->diffForHumans() : 'never' }}
                    </span>
                </p>
            </div>

            <div class="flex items-center gap-2.5 max-sm:w-full max-sm:flex-wrap">
                <form method="GET" action="{{ route('admin.omics.analytics.customer') }}">
                    <label for="analytics-year" class="sr-only">Filter by year</label>
                    <select
                        id="analytics-year"
                        name="year"
                        class="custom-select min-h-[39px] rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        onchange="this.form.submit()"
                    >
                        @foreach ($data['availableYears'] as $year)
                            <option value="{{ $year }}" @selected($data['selectedYear'] === $year)>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <form method="POST" action="{{ route('admin.omics.analytics.sync') }}">
                    @csrf
                    <button type="submit" class="primary-button">
                        <span class="icon-system-generate text-lg"></span>
                        Sync Now
                    </button>
                </form>
            </div>
        </div>

        <div>
            <!-- KPI STRIP (Current Year) -->
            <div class="section-head"><span class="num"></span><h2>Selected Year Metrics ({{ $data['selectedYear'] }})</h2><span class="rule"></span></div>
            <div class="kpi-strip">
              <div class="kpi"><div class="label">Total customers ({{ $data['selectedYear'] }})</div><div class="value">{{ number_format($data['totalCustomers']) }}</div><div class="delta up">Users with enrollments</div></div>
              <div class="kpi"><div class="label">Avg. customers / month</div><div class="value">{{ number_format($data['totalCustomers'] / 12) }}</div><div class="delta up">For selected year</div></div>
              <div class="kpi"><div class="label">Avg. satisfaction rating</div><div class="value">{{ $data['avgSatisfaction'] ?: '0' }} / 5</div><div class="delta up">For selected year</div></div>
              <div class="kpi"><div class="label">Countries reached</div><div class="value">{{ $data['countriesReached'] }}</div><div class="delta up">For selected year</div></div>
            </div>

            <!-- KPI STRIP (System Wide) -->
            <div class="section-head" style="margin-top:20px;"><span class="num"></span><h2>System-Wide Metrics (All-time)</h2><span class="rule"></span></div>
            <div class="kpi-strip">
              <div class="kpi"><div class="label">Total System Users</div><div class="value">{{ number_format($data['systemTotalUsers']) }}</div><div class="delta up">All registered accounts</div></div>
              <div class="kpi"><div class="label">Users with Enrollments</div><div class="value">{{ number_format($data['systemUsersWithEnrollments']) }}</div><div class="delta up">Enrolled in at least 1 course</div></div>
              <div class="kpi"><div class="label">Total Course Enrollments</div><div class="value">{{ number_format($data['systemTotalEnrollments']) }}</div><div class="delta up">Across all customers</div></div>
              <div class="kpi"><div class="label">Total System Feedbacks</div><div class="value">{{ number_format($data['systemTotalFeedback']) }}</div><div class="delta up">Avg Rating: {{ $data['systemAvgRating'] ?: '0' }} / 5</div></div>
            </div>
    
            <!-- 1. ACQUISITION & TRENDS -->
            <div class="section-head"><span class="num">01</span><h2>Customer Acquisition &amp; Trends</h2><span class="rule"></span></div>
            <div class="grid cols-2">
              <div class="card">
                <div class="card-head">
                  <div class="card-title">Customers by Month <span class="hint">Jan – Dec {{ $data['selectedYear'] }}</span></div>
                  <div class="card-kpi" id="cust-month-total">{{ number_format($data['totalCustomers']) }}</div>
                </div>
                <div class="chart-wrap h-260"><canvas id="chartCustMonth"></canvas></div>
              </div>
              <div class="card">
                <div class="card-head"><div class="card-title">Year-over-Year <span class="hint">Historical customer trend</span></div></div>
                <div class="chart-wrap h-260"><canvas id="chartCustYoY"></canvas></div>
              </div>
            </div>
    
            <!-- 2. DEMOGRAPHICS -->
            <div class="section-head"><span class="num">02</span><h2>Customer Demographics &amp; Segments</h2><span class="rule"></span></div>
            <div class="card">
                <div class="card-head"><div class="card-title">Customers by Country <span class="hint">Hover a country for its customer count</span></div></div>
                <div id="mapCust" class="map-box"></div>
                <div class="map-legend">Fewer customers <span class="swatch"></span> More customers</div>
            </div>

            <div class="grid cols-equal" style="margin-top:16px;">
              <div class="card">
                <div class="card-head"><div class="card-title">Top 10 Countries by Customers</div></div>
                <div class="chart-wrap h-300"><canvas id="chartCustCountries"></canvas></div>
              </div>
              <div class="card">
                <div class="card-head"><div class="card-title">Top Organizations by Customers <span class="hint">Top 10 institutions</span></div></div>
                <div class="chart-wrap h-300"><canvas id="chartCustOrgs"></canvas></div>
              </div>
            </div>
    
            <!-- 3. ENGAGEMENT -->
            <div class="section-head"><span class="num">03</span><h2>Engagement Performance by Product</h2><span class="rule"></span></div>
            <div id="engagementByProduct"></div>
            
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jsvectormap/1.4.3/css/jsvectormap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsvectormap/1.4.3/js/jsvectormap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsvectormap/1.4.3/maps/world.js"></script>
    
    <script>
    window.addEventListener('load', function() {
        setTimeout(function() {
            const serverData = @json($data);
            
            /* Chart.js Config */
    Chart.defaults.font.size = 11.5;
    Chart.defaults.color = '#6B7686';
    
    const TEAL = '#2158C9', TEAL_DEEP='#123B8F', TEAL_LIGHT='#8EB8F5', AMBER='#4F8FE0', SLATE='#B7C0CC';
    const GRID = { color:'#EEF1F2' };
    
    function barChart(ctx, labels, data, opts={}){
     try{
      return new Chart(ctx, {
        type:'bar',
        data:{ labels, datasets:[{ data, backgroundColor: opts.color || TEAL, borderRadius:5, maxBarThickness: opts.thickness || 34 }] },
        options:{
          indexAxis: opts.horizontal ? 'y' : 'x',
          responsive:true, maintainAspectRatio:false,
          plugins:{ legend:{display:false}, tooltip:{ backgroundColor:'#171D24', padding:10, cornerRadius:6, titleFont:{weight:600} } },
          scales:{
            x:{ grid: opts.horizontal ? GRID : {display:false}, border:{display:false} },
            y:{ grid: opts.horizontal ? {display:false} : GRID, border:{display:false} }
          }
        }
      });
     }catch(err){ console.error('Chart render failed:', err); }
    }
    
    /* Month Charts */
    barChart(document.getElementById('chartCustMonth'), serverData.months, serverData.customersByMonth, {color:AMBER});
    
    /* YoY */
    function yoyChart(ctx, totalsObj, color){
     try{
      const labels = Object.keys(totalsObj);
      const data = Object.values(totalsObj);
      new Chart(ctx, {
        type:'line',
        data:{ labels, datasets:[{
          data, borderColor:color, backgroundColor: color+'22', fill:true, tension:.35,
          pointBackgroundColor:'#fff', pointBorderColor:color, pointBorderWidth:2, pointRadius:5
        }]},
        options:{
          responsive:true, maintainAspectRatio:false,
          plugins:{ legend:{display:false}, tooltip:{ backgroundColor:'#171D24', padding:10, cornerRadius:6 } },
          scales:{ x:{ grid:{display:false}, border:{display:false} }, y:{ grid:GRID, border:{display:false} } }
        }
      });
     }catch(err){ console.error('Chart render failed:', err); }
    }
    yoyChart(document.getElementById('chartCustYoY'), serverData.customersByYear, AMBER);
    
    /* Organizations */
    barChart(document.getElementById('chartCustOrgs'), serverData.orgLabels, serverData.orgData, {horizontal:true, color:AMBER});
    
    /* Countries */
    barChart(document.getElementById('chartCustCountries'), serverData.countryLabels, serverData.countryData, {horizontal:true, color:AMBER});
    
    try{
      const mapEl = document.getElementById('mapCust');
      const sizeMapBox = () => {
        if (! mapEl) return;
        const width = mapEl.clientWidth || mapEl.parentElement?.clientWidth || 0;
        if (! width) return;
        const ratio = window.matchMedia('(max-width: 640px)').matches ? 1.6 : 2.2;
        const height = Math.max(260, Math.min(620, Math.round(width / ratio)));
        mapEl.style.width = '100%';
        mapEl.style.height = height + 'px';
      };

      sizeMapBox();

      const countryMap = new jsVectorMap({
        selector: '#mapCust',
        map: 'world',
        backgroundColor: 'transparent',
        draggable: true,
        zoomButtons: false,
        zoomOnScroll: false,
        selectedRegions: [],
        regionStyle: {
          initial: { fill:'#E4E8EB', stroke:'#fff', strokeWidth:0.5 },
          hover: { fill: '#4F8FE0', cursor:'pointer' }
        },
        series:{
          regions:[{
            attribute: 'fill',
            values: serverData.countryMapData || {},
            scale: ['#E1EAFB', '#2158C9'],
            // Anchor at 0 so a single country (e.g. count=1) still gets a visible color.
            min: 0,
            normalizeFunction: 'linear'
          }]
        },
        onLoaded(map){
          sizeMapBox();
          map.updateSize();
          requestAnimationFrame(() => {
            sizeMapBox();
            map.updateSize();
          });
          window.addEventListener('resize', () => {
            sizeMapBox();
            map.updateSize();
          });
        },
        onRegionTooltipShow(event, tooltip, code){
          const count = (serverData.countryMapData || {})[code];
          if (tooltip && typeof tooltip.text === 'function'){
            tooltip.text(count ? `${tooltip.text()}: ${Number(count).toLocaleString()}` : `${tooltip.text()}: no data`, false);
          }
        }
      });

      // Extra pass after layout settles (sticky header / fonts).
      setTimeout(() => {
        sizeMapBox();
        countryMap && countryMap.updateSize && countryMap.updateSize();
      }, 50);
      setTimeout(() => {
        sizeMapBox();
        countryMap && countryMap.updateSize && countryMap.updateSize();
      }, 300);
    }catch(err){ console.error('Map render failed:', err); }
    
    // Render Datatable for Products
    const container = document.getElementById('engagementByProduct');
    const products = serverData.productStats || [];
    
    if(products.length > 0) {
        const maxEnrolls = Math.max(...products.map(p => p.enrollments));
        
        let html = `
        <div class="analytics-table-wrap">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Product Name</th>
                        <th>Type</th>
                        <th style="width:200px">Enrollments</th>
                        <th style="width:120px">Feedbacks</th>
                        <th style="width:150px">Avg Rating</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        products.forEach((p, i) => {
            const enrolls = parseInt(p.enrollments) || 0;
            const feedbacks = parseInt(p.feedbacks_count) || 0;
            const rating = parseFloat(p.avg_rating) || 0;
            const pct = (enrolls / (maxEnrolls || 1)) * 100;
            
            let ratingHtml = '<span style="color:#9ca3af;font-size:12px">No rating</span>';
            if (rating > 0) {
                const stars = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
                ratingHtml = `<span class="stars-text">${stars} ${rating.toFixed(1)}</span>`;
            }
            
            html += `
                <tr>
                    <td class="num">${i + 1}</td>
                    <td style="font-weight:500">${p.product_name}</td>
                    <td><span class="badge-type" style="margin-left:0">${p.product_type || 'course'}</span></td>
                    <td>
                        <div class="progress-cell">
                            <div class="bar-bg"><div class="bar-fg" style="width:${pct}%"></div></div>
                            <span class="num">${enrolls.toLocaleString()}</span>
                        </div>
                    </td>
                    <td class="num">${feedbacks.toLocaleString()}</td>
                    <td>${ratingHtml}</td>
                </tr>
            `;
        });
        
        html += `</tbody></table></div>`;
        container.innerHTML = html;
    } else {
        container.innerHTML = '<div style="padding:40px; text-align:center; color:#6b7280; background:#f9fafb; border-radius:8px; margin-top:16px;">No engagement data available for the selected period.</div>';
    }
        
        }, 300); // Wait 300ms for Vue to finish mounting
    });
    </script>
    @endpush
</x-admin::layouts>
