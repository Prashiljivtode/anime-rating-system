<?php
session_start();
require_once "../security.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Anime - AniRate</title>
<style>
body{margin:0;padding:40px;background:#111;color:#fff;font-family:Arial,sans-serif}h1{color:#ff4d8d}.search-box{margin-bottom:30px}input{width:350px;max-width:65vw;padding:14px;border-radius:8px;border:none;font-size:16px}button{padding:14px 25px;background:#ff4d8d;color:#fff;border:0;border-radius:8px;cursor:pointer;font-size:16px}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:25px}.card{background:#1d1d1d;padding:15px;border-radius:12px}.card img{width:100%;height:300px;object-fit:cover;border-radius:10px;background:#292929}.card h2{font-size:20px;color:#ff4d8d}.info{color:#ccc;font-size:14px;line-height:1.5}.add-btn{display:block;width:100%;margin-top:15px;padding:10px;background:#ff4d8d;color:#fff;border:0;border-radius:8px;cursor:pointer;box-sizing:border-box}.error{color:#ff6666;margin:20px 0}.api-note{color:#888;font-size:13px;margin-top:-18px;margin-bottom:25px}.status{color:#aaa;margin:15px 0}@media(max-width:600px){body{padding:20px}input{width:100%;max-width:none;box-sizing:border-box;margin-bottom:10px}}
</style>
</head>
<body>
<h1>🎌 Search Anime</h1>
<div class="api-note">Powered by AniList GraphQL API</div>
<div class="search-box">
<form id="searchForm">
<input id="searchInput" type="text" placeholder="Enter anime name..." required>
<button type="submit">🔍 Search</button>
</form>
</div>
<div id="status" class="status"></div>
<div id="results" class="grid"></div>
<script>
const endpoint = 'https://graphql.anilist.co';
const query = `
query ($search: String, $page: Int, $perPage: Int) {
  Page(page: $page, perPage: $perPage) {
    media(type: ANIME, search: $search, isAdult: false) {
      id
      title { romaji english native }
      description(asHtml: false)
      episodes
      startDate { year }
      genres
      studios(isMain: true) { nodes { name } }
      coverImage { large medium }
    }
  }
}`;

function esc(v){const d=document.createElement('div');d.textContent=v??'';return d.innerHTML;}
function clean(v){return (v??'').replace(/<[^>]*>/g,'').trim();}
function makeForm(a){
  const title = a.title?.english || a.title?.romaji || a.title?.native || 'Unknown Anime';
  const description = clean(a.description || 'No description available.');
  const episodes = a.episodes ?? '';
  const year = a.startDate?.year ?? '';
  const genre = (a.genres || []).join(', ');
  const studio = a.studios?.nodes?.[0]?.name || '';
  const image = a.coverImage?.large || a.coverImage?.medium || '';
  const card=document.createElement('div'); card.className='card';
  const shortDesc=description.length>180?description.slice(0,180)+'...':description;
  card.innerHTML=`
    ${image?`<img src="${esc(image)}" alt="${esc(title)}" loading="lazy">`:''}
    <h2>${esc(title)}</h2>
    <div class="info">🎬 Episodes: ${esc(episodes||'N/A')}<br><br>📅 Year: ${esc(year||'N/A')}<br><br>🎭 Genre: ${esc(genre||'N/A')}<br><br>🏢 Studio: ${esc(studio||'N/A')}</div>
    <p>${esc(shortDesc)}</p>
    <form method="POST" action="save_anime.php">
      <input type="hidden" name="title" value="${esc(title)}">
      <input type="hidden" name="description" value="${esc(description)}">
      <input type="hidden" name="genre" value="${esc(genre)}">
      <input type="hidden" name="episodes" value="${esc(episodes)}">
      <input type="hidden" name="release_year" value="${esc(year)}">
      <input type="hidden" name="studio" value="${esc(studio)}">
      <input type="hidden" name="image_url" value="${esc(image)}">
      <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
      <button type="submit" class="add-btn">➕ Add Anime</button>
    </form>`;
  return card;
}

document.getElementById('searchForm').addEventListener('submit', async (ev)=>{
  ev.preventDefault();
  const search=document.getElementById('searchInput').value.trim();
  if(!search)return;
  const status=document.getElementById('status');
  const results=document.getElementById('results');
  results.innerHTML=''; status.textContent='Searching AniList...';
  try{
    const response=await fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({query,variables:{search,page:1,perPage:10}})});
    const json=await response.json();
    if(!response.ok) throw new Error(json?.errors?.[0]?.message || `HTTP ${response.status}`);
    const items=json?.data?.Page?.media || [];
    if(!items.length){status.textContent='No anime found.';return;}
    status.textContent=`Found ${items.length} result(s).`;
    items.forEach(a=>results.appendChild(makeForm(a)));
  }catch(err){status.textContent='❌ Anime API Error: '+err.message;}
});
</script>
</body>
</html>
