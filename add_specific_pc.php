body{
margin:0;
font-family:Arial;
background:#0b1320;
color:white;
}

.topbar{
display:flex;
justify-content:space-between;
align-items:center;
padding:15px 30px;
background:#08111f;
border-bottom:1px solid #1c2b45;
}
.navbar{
display:flex;
justify-content:space-between;
align-items:center;
padding:12px 30px;
background:#071426;
border-bottom:1px solid #132742;
color:white;
}

.login-logo{
font-size:32px;
}

.logo span{
color:#9fdcff;
}

.logo b{
color:#00e0ff;
}

.nav-links{
display:flex;
gap:20px;
}

.nav-links a{
color:#9fb3d9;
text-decoration:none;
padding:8px 16px;
border-radius:10px;
}

.nav-links .active{
background:#0c2d44;
border:1px solid #1fb6ff;
color:#1fb6ff;
}

.nav-right{
display:flex;
align-items:center;
gap:15px;
}

.status span{
padding:5px 12px;
border-radius:20px;
font-size:12px;
}

.free{
background:#0f3f2e;
color:#19ff9c;
}

.activepc{
background:#4a2b06;
color:#ffae00;
}

.user{
background:#1a263a;
padding:6px 12px;
border-radius:8px;
}

.logout{
background:#5c0000;
padding:6px 12px;
border-radius:8px;
color:#ff8080;
text-decoration:none;
}

.menu a{
margin-left:20px;
color:#9fb3d9;
text-decoration:none;
}

.menu .active{
color:#00d4ff;
border:1px solid #00d4ff;
padding:5px 12px;
border-radius:8px;
}

.container{
padding:30px;
}
/* COUNTER BOTTOM STATS */

.counter-stats{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:20px;
margin-top:30px;
}

.stat-card{
background:#1e2c40;
border:1px solid #2b4666;
border-radius:12px;
padding:25px;
text-align:center;
}

.stat-card h2{
font-size:26px;
margin-bottom:5px;
}

.stat-card p{
font-size:13px;
color:#8aa0c5;
}

/* COLORS */

.revenue h2{
color:#00d4ff;
}

.sessions h2{
color:#00ff88;
}

.prints h2{
color:#c084ff;
}

/* MAIN LAYOUT */

.main-layout{
display:grid;
grid-template-columns: 1fr 320px;
gap:25px;
}

/* LEFT SECTION */

.left-section{
width:100%;
}

/* RIGHT SIDEBAR */

.right-sidebar{
display:flex;
flex-direction:column;
gap:20px;
}

/* PANEL */

.panel-card{
background:#1b2a40;
border:1px solid #2c4566;
border-radius:12px;
padding:20px;
}

/* PRINT BUTTONS */

.print-type{
display:flex;
gap:10px;
margin-bottom:10px;
}

.print-type button{
flex:1;
padding:10px;
background:#0e1a2b;
border:1px solid #2c4566;
border-radius:8px;
color:white;
cursor:pointer;
}

.print-type .active{
border-color:#00d4ff;
}

/* INPUT */

.panel-card input{
width:100%;
padding:8px;
margin-top:5px;
margin-bottom:10px;
background:#0e1a2b;
border:1px solid #2c4566;
border-radius:6px;
color:white;
}

/* BUTTON */

.primary-btn{
width:100%;
padding:10px;
background:#1fb6d9;
border:none;
border-radius:8px;
cursor:pointer;
margin-top:10px;
}

.pc-grid{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-top:20px;
}

.pc-card{
background:#0f1c2e;
border:1px solid #16334f;
border-radius:12px;
padding:20px;
text-align:center;
transition:0.3s;
}

.pc-card:hover{
border-color:#00d4ff;
transform:scale(1.03);
}

.status{
display:inline-block;
margin:10px 0;
padding:5px 12px;
background:#0c3d2b;
color:#00ff9c;
border-radius:20px;
font-size:13px;
}

.start-btn{
display:block;
margin-top:10px;
padding:8px;
background:#00d4ff;
color:#000;
border-radius:6px;
text-decoration:none;
font-weight:bold;
}
/* LOGIN PAGE */

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 30px;
    background-color: #0a0e14;
    border-bottom: 1px solid #1e293b;
    position: relative;
    height: 60px; /* Force a consistent height */
    box-sizing: border-box; /* Ensures padding doesn't add to the width */
}
.logo{
font-size:32px;
}

.login-box{
width:400px;
margin:80px auto;
background:#0f1c2e;
padding:40px;
border-radius:12px;
border:1px solid #1a2f4d;
box-shadow:0 0 20px rgba(0,0,0,0.5);
}

.login-box h2{
margin-bottom:10px;
}

.login-sub{
color:#8aa0c5;
margin-bottom:25px;
}

.input-group{
margin-bottom:20px;
display:flex;
flex-direction:column;
}

.input-group label{
margin-bottom:5px;
font-size:14px;
color:#9fb3d9;
}

.input-group input{
padding:10px;
border-radius:6px;
border:1px solid #1c2f4a;
background:#08111f;
color:white;
}

.input-group input:focus{
outline:none;
border-color:#00d4ff;
}

.login-btn{
width:100%;
padding:12px;
background:#00d4ff;
border:none;
border-radius:6px;
color:#001018;
font-weight:bold;
cursor:pointer;
margin-top:10px;
}

.login-btn:hover{
background:#00b8df;
}

.demo{
margin-top:25px;
font-size:14px;
color:#9fb3d9;
}
/* LOGIN WRAPPER */

.login-wrapper{
text-align:center;
padding-top:60px;
}

/* GRID BACKGROUND */

body::before {
    content: "";
    position: fixed;
    width: 100%;
    height: 100%;
    background-image:
        linear-gradient(#10233d 1px, transparent 1px),
        linear-gradient(90deg, #10233d 1px, transparent 1px);
    background-size: 60px 60px;
    opacity: 0.3;
    z-index: -1;
    pointer-events: none; /* Add this so it doesn't block mouse clicks */
}

/* LOGIN HEADER */

.login-header{
text-align:center;
margin-bottom:30px;
}

.logo-box{
width:80px;
height:80px;
margin:auto;
background:#0c9db5;
border-radius:18px;
display:flex;
align-items:center;
justify-content:center;
font-size:40px;
}

/* LOGIN CARD */

.login-card{
width:420px;
margin:40px auto;
background:#1a2a3f;
padding:40px;
border-radius:20px;
box-shadow:0 0 40px rgba(0,0,0,0.6);
text-align:left;
}

.login-card h2{
margin-bottom:5px;
}

.login-card .login-sub{
color:#9fb3d9;
margin-bottom:25px;
}

/* INPUT IMPROVEMENTS */

.login-card input{
width:100%;
padding:14px;
border-radius:10px;
border:1px solid #253c5e;
background:#081626;
color:white;
}

.login-card input:focus{
outline:none;
border-color:#00d4ff;
}

/* LOGIN BUTTON */

.login-card .login-btn{
width:100%;
padding:14px;
border:none;
border-radius:10px;
background:#1f7285;
color:white;
font-size:16px;
cursor:pointer;
margin-top:10px;
}

.login-card .login-btn:hover{
background:#1ba0b9;
}


.login-card .demo{
margin-top:30px;
border-top:1px solid #2e4466;
padding-top:20px;
font-size:14px;
color:#9fb3d9;
}

/* DASHBOARD CARDS */

.dashboard-cards{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-top:30px;
}

.card{
background:#1a2a3f;
border:1px solid #243b5e;
border-radius:16px;
padding:25px;
display:flex;
align-items:center;
gap:20px;
transition:0.3s;
}

.card:hover{
border-color:#00d4ff;
transform:translateY(-3px);
}

.card-icon{
width:50px;
height:50px;
border-radius:12px;
display:flex;
align-items:center;
justify-content:center;
font-size:22px;
}

/* ICON COLORS */

.revenue{
background:#0c3b4a;
color:#00e0ff;
}

.sessions{
background:#0f3f2e;
color:#19ff9c;
}

.print{
background:#3a1a5c;
color:#c084ff;
}

.stats{
background:#4a2b06;
color:#ffae00;
}

.card-info h2{
margin:0;
font-size:28px;
color:#00e0ff;
}

.card-info p{
margin-top:5px;
color:#8aa0c5;
font-size:14px;
}

.admin-badge{
background:#7a0000;
color:white;
padding:4px 10px;
border-radius:20px;
font-size:11px;
margin-left:10px;
}

.section-title{
margin-top:15px;
color:#00d9ff;
font-size:12px;
letter-spacing:1px;
}

.rate-row{
display:flex;
gap:10px;
margin-top:10px;
}

.rate-box{
flex:1;
}

.rate-box input{
width:100%;
padding:8px;
background:#0e1a2b;
border:1px solid #2c4566;
border-radius:8px;
color:white;
}

.billing-buttons{
display:flex;
gap:10px;
margin-top:10px;
}

.billing{
flex:1;
padding:10px;
background:#0e1a2b;
border:1px solid #2c4566;
border-radius:8px;
cursor:pointer;
}

.billing.active{
border-color:#00e0ff;
}

.rate-summary{
background:#0e1a2b;
padding:10px;
border-radius:8px;
margin-top:10px;
font-size:13px;
}

.rate-summary div{
display:flex;
justify-content:space-between;
margin-bottom:5px;
}

.rate-buttons{
display:flex;
gap:10px;
margin-top:15px;
}

.reset-btn{
flex:1;
padding:10px;
background:#16263c;
border:none;
border-radius:8px;
color:white;
}

.save-btn{
flex:2;
padding:10px;
background:#17b5c7;
border:none;
border-radius:8px;
color:white;
}

.receipt-modal{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.7);
display:none;
justify-content:center;
align-items:center;
}

.receipt-box{
background:white;
color:black;
padding:25px;
width:300px;
border-radius:10px;
text-align:center;
}

.receipt-box hr{
margin:10px 0;
}
/* MODAL BACKGROUND */
.session-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

/* MODAL BOX */
.session-box {
  background: #0f172a;
  padding: 25px 30px;
  border-radius: 12px;
  width: 320px;
  text-align: center;
  color: #fff;
  box-shadow: 0 0 20px rgba(0,255,255,0.2);
  animation: popIn 0.2s ease;
}

.session-box h2 {
  margin-bottom: 10px;
}

.session-box p {
  margin-bottom: 20px;
  color: #cbd5f5;
}

/* BUTTONS */
.session-actions {
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.cancel-btn {
  flex: 1;
  background: #334155;
  border: none;
  padding: 10px;
  border-radius: 8px;
  color: #fff;
  cursor: pointer;
}

.confirm-btn {
  flex: 1;
  background: #06b6d4;
  border: none;
  padding: 10px;
  border-radius: 8px;
  color: #000;
  font-weight: bold;
  cursor: pointer;
}

.confirm-btn:hover {
  background: #22d3ee;
}

/* ANIMATION */
@keyframes popIn {
  from {
    transform: scale(0.8);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
/* PC Status Colors */
.status.active {
    background: #4a2b06;
    color: #ffae00; /* Orange/Yellow for In Use */
}

.status.available {
    background: #0f3f2e;
    color: #19ff9c; /* Green for Available */
}
.panel-card label {
    display: block;
    font-size: 13px;
    color: #8aa0c5;
    margin-bottom: 5px;
}

.panel-card h3 {
    margin-top: 0;
    color: #00e0ff;
    border-bottom: 1px solid #2c4566;
    padding-bottom: 10px;
}

/* Success/Error Messages */
.msg-banner {
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    background: #0f3f2e;
    color: #19ff9c;
    border: 1px solid #19ff9c;
}
/* This container holds both the grid/stats and the sidebar */
.main-layout {
    display: flex; 
    flex-wrap: nowrap; /* CRITICAL: This prevents the sidebar from dropping down */
    gap: 20px;
    align-items: flex-start;
    width: 100%;
}

/* This is your PC Grid and Stat Cards area */
.left-section {
    flex: 1; /* This tells the section to take up all remaining space */
    min-width: 0; /* This allows the section to shrink so it doesn't push the sidebar */
}

/* This is your Sidebar (Print/Rates) */
.right-section {
    width: 350px; /* Gives the sidebar a fixed, reliable width */
    flex-shrink: 0; /* Prevents the sidebar from getting squashed */
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 30px;
    background-color: #0a0e14;
    border-bottom: 1px solid #1e293b;
    position: relative;
}

/* The subtle red glow/line you liked */
.header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, #ff4d4d, transparent);
}

.nav-item {
    text-decoration: none;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    padding: 10px 5px;
}

.nav-item.active {
    color: #ff4d4d;
    border-bottom: 2px solid #ff4d4d;
}

#systemClock {
    color: #38bdf8;
    font-family: monospace;
    background: rgba(56, 189, 248, 0.1);
    padding: 5px 12px;
    border-radius: 4px;
    font-weight: bold;
    margin-right: 10px;
}
