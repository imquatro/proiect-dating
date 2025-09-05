# Necesită: PyQt5 și PyQtWebEngine
# pip install PyQt5 PyQtWebEngine
# Rulează: python mybrowser.py

import json
import os
import sys
import urllib.parse
from datetime import datetime
from pathlib import Path

from PyQt5.QtCore import QUrl, Qt, pyqtSignal
from PyQt5.QtGui import QIcon, QFont, QPalette, QColor
from PyQt5.QtWidgets import (
    QApplication, QMainWindow, QToolBar, QAction, QLineEdit, QFileDialog,
    QMessageBox, QInputDialog, QWidget, QVBoxLayout, QDialog, QLabel, QSpinBox,
    QPushButton, QHBoxLayout, QTabWidget, QCheckBox, QFormLayout, QToolButton,
    QColorDialog, QMenu
)
from PyQt5.QtWebEngineWidgets import QWebEngineView, QWebEngineProfile, QWebEngineSettings, QWebEnginePage
from PyQt5.QtWebEngineCore import QWebEngineUrlRequestInterceptor

CONFIG_FILE = "browser_config.json"
STARTPAGE_FILE = "startpage.html"

DEFAULT_CONFIG = {
    "home_url": "https://www.google.com",
    "zoom": 1.0,
    "ui_font_px": 14,
    "bookmark_font_px": 14,
    "dark_mode": True,
    "persist_tabs": True,
    "auto_import_chrome": True,
    "session": {"tabs": [], "current_index": 0},
    "popup_whitelist": [],
    "popup_blacklist": [],
    "adblock_whitelist": [],
    "accent_color": "#6aa0ff",
    "features": {
        "block_ads": True,
        "block_popups": True,
        "ask_on_popup": True,
        "yt_autoskip": True
    },
    "bookmarks": [
        {"title": "Google", "url": "https://www.google.com"},
        {"title": "YouTube", "url": "https://www.youtube.com"},
        {"title": "GitHub", "url": "https://github.com"}
    ]
}

UA_CHROME = (
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
    "AppleWebKit/537.36 (KHTML, like Gecko) "
    "Chrome/120.0.0.0 Safari/537.36"
)

AD_HOST_PATTERNS = [
    "doubleclick.net",
    "googlesyndication.com",
    "googletagservices.com",
    "googletagmanager.com",
    "googleadservices.com",
    "pagead2.googlesyndication.com",
    "adservice.google.com",
    "ads.youtube.com",
    "pubmatic.com",
    "rubiconproject.com",
    "criteo.com",
    "scorecardresearch.com",
    "adnxs.com",
    "taboola.com",
    "outbrain.com",
    "moatads.com",
]

YOUTUBE_AD_PATH_HINTS = [
    "/api/stats/ads",
    "/generate_204?atari=",
    "/pagead/",
    "/ptracking",
]


def load_config():
    if not os.path.exists(CONFIG_FILE):
        save_config(DEFAULT_CONFIG)
        return json.loads(json.dumps(DEFAULT_CONFIG))
    try:
        with open(CONFIG_FILE, "r", encoding="utf-8") as f:
            data = json.load(f)
        # deep merge minimal
        cfg = json.loads(json.dumps(DEFAULT_CONFIG))
        for k, v in data.items():
            if isinstance(v, dict) and k in cfg:
                cfg[k].update(v)
            else:
                cfg[k] = v
        return cfg
    except Exception:
        return json.loads(json.dumps(DEFAULT_CONFIG))


def save_config(cfg):
    try:
        with open(CONFIG_FILE, "w", encoding="utf-8") as f:
            json.dump(cfg, f, indent=2, ensure_ascii=False)
    except Exception as e:
        print("Eroare salvare config:", e)


def domain_from_url(url: str) -> str:
    try:
        return urllib.parse.urlparse(url).netloc
    except Exception:
        return url


def chrome_bookmarks_paths():
    local = os.environ.get("LOCALAPPDATA") or str(Path.home())
    paths = [
        os.path.join(local, r"Google\Chrome\User Data\Default\Bookmarks"),
        os.path.join(local, r"Microsoft\Edge\User Data\Default\Bookmarks"),
        os.path.join(local, r"BraveSoftware\Brave-Browser\User Data\Default\Bookmarks"),
    ]
    return [p for p in paths if os.path.exists(p)]


def import_chromium_bookmarks() -> list:
    items = []
    for p in chrome_bookmarks_paths():
        try:
            with open(p, "r", encoding="utf-8") as f:
                data = json.load(f)
            roots = data.get("roots", {})
            for key in ("bookmark_bar", "other", "synced"):
                node = roots.get(key)
                if node:
                    items.extend(_flatten_bookmarks(node))
        except Exception as e:
            print("Eșec import bookmarks din:", p, e)
    # elimină duplicate după url
    seen = set()
    uniq = []
    for it in items:
        u = it.get("url")
        if u and u not in seen:
            uniq.append(it)
            seen.add(u)
    return uniq


def _flatten_bookmarks(node):
    out = []
    if node.get("type") == "url":
        out.append({"title": node.get("name", "Link"), "url": node.get("url", "")})
    for ch in node.get("children", []) or []:
        out.extend(_flatten_bookmarks(ch))
    return out


def build_startpage(cfg):
    bg_url = "https://source.unsplash.com/1600x900/?nature,landscape,abstract"
    cards_html = []
    for bm in cfg.get("bookmarks", []):
        dom = domain_from_url(bm.get("url", ""))
        fav = f"https://www.google.com/s2/favicons?sz=64&domain={urllib.parse.quote(dom)}"
        title = bm.get("title", dom)
        url = bm.get("url", "")
        cards_html.append(f"""
        <a class='card' href='{url}' target='_self'>
            <div class='icon'><img src='{fav}' alt=''></div>
            <div class='title'>{title}</div>
            <div class='subtitle'>{dom}</div>
        </a>
        """)
    cards = "\n".join(cards_html) or "<div class='empty'>N-ai bookmarks încă. Apasă ★ pentru a adăuga.</div>"

    accent = cfg.get("accent_color", DEFAULT_CONFIG["accent_color"])

    script = """
  function tick() {
    const d = new Date();
    document.getElementById('clock').textContent = d.toLocaleString();
  }
  setInterval(tick, 1000); tick();
  const q = document.getElementById('q');
  q.addEventListener('keydown', (e)=>{
    if(e.key==='Enter') {
      const s = q.value.trim(); if(!s) return;
      const url = s.startsWith('http') ? s : 'https://www.google.com/search?q=' + encodeURIComponent(s);
      location.href = url;
    }
  });
"""

    html = f"""
<!doctype html>
<html>
<head>
<meta charset='utf-8'/>
<meta name='viewport' content='width=device-width, initial-scale=1'/>
<title>Start</title>
<style>
  :root {{ --bg: rgba(20,20,20,.75); --fg: #ffffff; --accent: {accent}; --glass: rgba(255,255,255,.08); }}
  * {{ box-sizing: border-box; }}
  body {{ margin: 0; font-family: -apple-system, Segoe UI, Roboto, Inter, sans-serif; color: var(--fg);
         min-height:100vh; background: #0e0e0e; }}
  .hero {{ position: relative; min-height: 100vh; display: grid; place-items: center; }}
  .hero::before {{ content:''; position:absolute; inset:0; background:url('{bg_url}') center/cover no-repeat fixed; filter: brightness(.7) saturate(1.1); }}
  .scrim {{ position:absolute; inset:0; background:linear-gradient(180deg, rgba(0,0,0,.45), rgba(0,0,0,.7)); }}
  .container {{ position:relative; width:min(1100px, 92vw); margin:auto; padding:36px; }}
  .glass {{ background: var(--glass); backdrop-filter: blur(14px) saturate(1.2); border:1px solid rgba(255,255,255,.12); border-radius:20px; box-shadow: 0 10px 30px rgba(0,0,0,.35); }}
  .top {{ display:flex; align-items:center; justify-content:space-between; padding:20px 24px; }}
  .logo {{ font-weight:700; letter-spacing:.5px; font-size:18px; opacity:.9 }}
  .clock {{ font-variant-numeric: tabular-nums; opacity:.9; }}
  .search {{ padding:8px; }}
  .search input {{ width:100%; padding:16px 18px; border-radius:16px; border:1px solid rgba(255,255,255,.15); background:rgba(0,0,0,.35); color:#fff; outline:none; font-size:16px; }}
  .grid {{ display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:16px; padding:20px; }}
  .card {{ display:block; padding:16px; border-radius:16px; background:rgba(0,0,0,.35); border:1px solid rgba(255,255,255,.12); text-decoration:none; color:#f0f0f0; transition: transform .18s ease, background .18s ease, border-color .18s ease; }}
  .card:hover {{ transform: translateY(-3px); background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.25); }}
  .icon img {{ width:28px; height:28px; border-radius:6px; }}
  .title {{ margin-top:10px; font-weight:600; }}
  .subtitle {{ opacity:.7; font-size:12px; margin-top:2px; }}
  .empty {{ opacity:.8; padding:24px; text-align:center; }}
  .footer {{ padding:12px 20px; opacity:.8; font-size:12px; text-align:right; }}
</style>
</head>
<body>
<section class='hero'>
  <div class='scrim'></div>
  <div class='container glass'>
    <div class='top'>
      <div class='logo'>✨ Start</div>
      <div class='clock' id='clock'></div>
    </div>
    <div class='search'>
      <input id='q' type='text' placeholder='Caută pe web… (Enter)' autofocus/>
    </div>
    <div class='grid'>
      {cards}
    </div>
    <div class='footer'>Imagine de fundal dinamică (Unsplash Source)</div>
  </div>
</section>
<script>
{script}
</script>
</body>
</html>
"""
    try:
        with open(STARTPAGE_FILE, "w", encoding="utf-8") as f:
            f.write(html)
    except Exception as e:
        print("NU am putut scrie startpage:", e)


class AdBlocker(QWebEngineUrlRequestInterceptor):
    def __init__(self, cfg_ref):
        super().__init__()
        self.cfg_ref = cfg_ref

    def interceptRequest(self, info):
        try:
            if not self.cfg_ref.get("features", {}).get("block_ads", True):
                return
            fp = ""
            try:
                fp = info.firstPartyUrl().host().lower()
            except Exception:
                pass
            if fp and fp in [h.lower() for h in self.cfg_ref.get("adblock_whitelist", [])]:
                return
            url = info.requestUrl().toString().lower()
            host = info.requestUrl().host().lower()
            if any(h in host for h in AD_HOST_PATTERNS):
                info.block(True); return
            if "youtube.com" in host:
                if any(p in url for p in YOUTUBE_AD_PATH_HINTS):
                    info.block(True); return
        except Exception:
            pass


class MyWebEnginePage(QWebEnginePage):
    popupRequested = pyqtSignal(QUrl)

    def __init__(self, profile, parent=None):
        super().__init__(profile, parent)

    def createWindow(self, windowType):
        page = MyWebEnginePage(self.profile(), self)
        page.urlChanged.connect(lambda u: self._maybe_handle_popup(u))
        return page

    def _maybe_handle_popup(self, url: QUrl):
        if not url.isEmpty():
            self.popupRequested.emit(url)


class PopupDialog(QDialog):
    def __init__(self, host, parent=None):
        super().__init__(parent)
        self.host = host
        self.decision = None
        self.setWindowFlags(Qt.FramelessWindowHint | Qt.Tool)
        layout = QVBoxLayout(self)
        layout.addWidget(QLabel(f"Site-ul {host} vrea să deschidă un popup."))
        btns = QHBoxLayout()
        allow = QPushButton("Allow")
        allow.clicked.connect(lambda: self._finish("allow"))
        allow_all = QPushButton("Allow Always")
        allow_all.clicked.connect(lambda: self._finish("allow_all"))
        deny = QPushButton("Deny")
        deny.clicked.connect(lambda: self._finish("deny"))
        deny_all = QPushButton("Deny Always")
        deny_all.clicked.connect(lambda: self._finish("deny_all"))
        for b in (allow, allow_all, deny, deny_all):
            btns.addWidget(b)
        layout.addLayout(btns)
        self.setStyleSheet("background:#333; color:#fff; padding:12px; border-radius:8px;")

    def _finish(self, res):
        self.decision = res
        self.accept()


class SettingsTab(QWidget):
    def __init__(self, parent, cfg, on_import_click):
        super().__init__(parent)
        self.cfg = cfg
        self.on_import_click = on_import_click

        lay = QVBoxLayout(self)
        title = QLabel("Setări Browser")
        title.setStyleSheet("font-size:18px; font-weight:600; margin:6px 0 12px 0;")
        lay.addWidget(title)

        form = QFormLayout()
        self.chk_ads = QCheckBox("Blochează reclame (generic)")
        self.chk_ads.setChecked(self.cfg["features"].get("block_ads", True))
        form.addRow(self.chk_ads)

        self.chk_yt = QCheckBox("YouTube auto-skip (apasă automat Skip Ads)")
        self.chk_yt.setChecked(self.cfg["features"].get("yt_autoskip", True))
        form.addRow(self.chk_yt)

        self.chk_pop = QCheckBox("Blochează pop-ups")
        self.chk_pop.setChecked(self.cfg["features"].get("block_popups", True))
        form.addRow(self.chk_pop)

        self.chk_ask = QCheckBox("Întreabă când o pagină vrea să deschidă un popup (Allow/Deny)")
        self.chk_ask.setChecked(self.cfg["features"].get("ask_on_popup", True))
        form.addRow(self.chk_ask)

        self.chk_import = QCheckBox("Importă automat bookmarks din Chrome/Edge la pornire")
        self.chk_import.setChecked(self.cfg.get("auto_import_chrome", True))
        form.addRow(self.chk_import)

        self.btn_accent = QPushButton()
        self.btn_accent.clicked.connect(self._pick_accent)
        self._update_accent_btn()
        form.addRow("Culoare accent", self.btn_accent)

        lay.addLayout(form)

        btns = QHBoxLayout()
        btn_import = QPushButton("Importă acum din Chrome/Edge")
        btn_import.clicked.connect(self.on_import_click)
        btn_save = QPushButton("Salvează setări")
        btn_save.clicked.connect(self._save)
        btns.addStretch(); btns.addWidget(btn_import); btns.addWidget(btn_save)
        lay.addLayout(btns)
        lay.addStretch()

    def _save(self):
        self.cfg["features"]["block_ads"] = self.chk_ads.isChecked()
        self.cfg["features"]["yt_autoskip"] = self.chk_yt.isChecked()
        self.cfg["features"]["block_popups"] = self.chk_pop.isChecked()
        self.cfg["features"]["ask_on_popup"] = self.chk_ask.isChecked()
        self.cfg["auto_import_chrome"] = self.chk_import.isChecked()
        save_config(self.cfg)
        self.parent().update_stylesheet()
        self.parent().apply_theme(self.cfg.get("dark_mode", True))
        build_startpage(self.cfg)
        QMessageBox.information(self, "Salvat", "Setările au fost salvate.")

    def _update_accent_btn(self):
        c = self.cfg.get("accent_color", DEFAULT_CONFIG["accent_color"])
        self.btn_accent.setText(c)
        self.btn_accent.setStyleSheet(f"background:{c}; color:#000;")

    def _pick_accent(self):
        col = QColorDialog.getColor(QColor(self.cfg.get("accent_color", DEFAULT_CONFIG["accent_color"])), self, "Alege culoare")
        if col.isValid():
            self.cfg["accent_color"] = col.name()
            self._update_accent_btn()


class Browser(QMainWindow):
    def __init__(self, cfg):
        super().__init__()
        self.cfg = cfg
        self.setWindowTitle("Browser-ul meu")
        self.resize(1280, 840)

        # Tabs
        self.tabs = QTabWidget()
        self.tabs.setMovable(True)
        self.tabs.setTabsClosable(True)
        self.tabs.tabCloseRequested.connect(self.close_tab)
        self.tabs.currentChanged.connect(self.on_tab_changed)
        self.tabs.tabBar().setContextMenuPolicy(Qt.CustomContextMenu)
        self.tabs.tabBar().customContextMenuRequested.connect(self.on_tab_context_menu)
        self.setCentralWidget(self.tabs)

        # plus și listă de taburi pe bara de taburi
        corner = QWidget()
        c_lay = QHBoxLayout(corner)
        c_lay.setContentsMargins(0, 0, 0, 0)
        c_lay.setSpacing(0)
        plus = QToolButton(corner)
        plus.setText("＋")
        plus.setToolTip("Tab nou (Ctrl+T)")
        plus.clicked.connect(self.open_start_in_new_tab)
        self.list_tabs_btn = QToolButton(corner)
        self.list_tabs_btn.setText("▾")
        self.list_tabs_btn.setToolTip("Selectează tabul")
        self.list_tabs_btn.clicked.connect(self.show_tab_list)
        c_lay.addWidget(plus)
        c_lay.addWidget(self.list_tabs_btn)
        self.tabs.setCornerWidget(corner, Qt.TopRightCorner)

        # Toolbars (modern look / glass)
        self.nav = QToolBar("Navigare")
        self.nav.setMovable(False)
        self.addToolBar(Qt.TopToolBarArea, self.nav)

        self.update_stylesheet()

        # Actions
        self.back_act = QAction("◀", self)
        self.back_act.triggered.connect(lambda: self.current_view() and self.current_view().back())
        self.nav.addAction(self.back_act)

        self.fwd_act = QAction("▶", self)
        self.fwd_act.triggered.connect(lambda: self.current_view() and self.current_view().forward())
        self.nav.addAction(self.fwd_act)

        self.reload_act = QAction("⟳", self)
        self.reload_act.triggered.connect(lambda: self.current_view() and self.current_view().reload())
        self.nav.addAction(self.reload_act)

        home_act = QAction("⌂", self)
        home_act.triggered.connect(self.go_home)
        self.nav.addAction(home_act)

        self.nav.addSeparator()

        newtab_act = QAction("＋", self)
        newtab_act.setToolTip("Tab nou (Ctrl+T)")
        newtab_act.triggered.connect(self.open_start_in_new_tab)
        newtab_act.setShortcut("Ctrl+T")
        self.nav.addAction(newtab_act)

        self.nav.addSeparator()

        zoom_out = QAction("−", self)
        zoom_out.triggered.connect(self.zoom_out)
        self.nav.addAction(zoom_out)

        zoom_reset = QAction("100%", self)
        zoom_reset.triggered.connect(self.zoom_reset)
        self.nav.addAction(zoom_reset)

        zoom_in = QAction("+", self)
        zoom_in.triggered.connect(self.zoom_in)
        self.nav.addAction(zoom_in)

        self.nav.addSeparator()

        self.url_bar = QLineEdit()
        self.url_bar.setPlaceholderText("Introdu URL sau caută…")
        self.url_bar.returnPressed.connect(self.navigate)
        self.nav.addWidget(self.url_bar)

        add_bm_act = QAction("★", self)
        add_bm_act.setToolTip("Adaugă la Bookmarks")
        add_bm_act.triggered.connect(self.add_bookmark)
        self.nav.addAction(add_bm_act)

        settings_tab_act = QAction("☰", self)
        settings_tab_act.setToolTip("Tab de Setări")
        settings_tab_act.triggered.connect(self.open_settings_tab)
        self.nav.addAction(settings_tab_act)

        self.dark_act = QAction("☾", self)
        self.dark_act.setToolTip("Comută Dark/Light")
        self.dark_act.triggered.connect(self.toggle_dark_mode)
        self.nav.addAction(self.dark_act)

        self.adblock_act = QAction("⛔", self)
        self.adblock_act.setToolTip("Toggle AdBlock pentru site-ul curent")
        self.adblock_act.triggered.connect(self.toggle_adblock_site)
        self.nav.addAction(self.adblock_act)

        # Bookmark toolbar
        self.bm_toolbar = QToolBar("Bookmarks")
        self.bm_toolbar.setMovable(False)
        self.addToolBar(Qt.TopToolBarArea, self.bm_toolbar)
        self.refresh_bookmarks_toolbar()

        # UA global backup
        try:
            profile = QWebEngineProfile.defaultProfile()
            profile.setHttpUserAgent(UA_CHROME)
        except Exception as e:
            print("NU am putut seta UA global:", e)

        # Ad-blocker
        self.interceptor = AdBlocker(self.cfg)
        QWebEngineProfile.defaultProfile().setUrlRequestInterceptor(self.interceptor)

        # Build start page asset
        build_startpage(self.cfg)

        # Auto-import bookmarks la pornire (opțional)
        if self.cfg.get("auto_import_chrome", True):
            self.try_import_bookmarks_first_time()

        # Restore session sau arată start page
        if self.cfg.get("persist_tabs", True) and self.cfg.get("session", {}).get("tabs"):
            self.restore_session()
        else:
            self.open_start_in_new_tab()

        # Fonturi + temă
        self.apply_fonts()
        self.apply_theme(self.cfg.get("dark_mode", True))
        self.update_adblock_action()

        # Shortcuts
        self.back_act.setShortcut("Alt+Left")
        self.fwd_act.setShortcut("Alt+Right")
        self.reload_act.setShortcut("Ctrl+R")

    # ====== import bookmarks ======
    def try_import_bookmarks_first_time(self):
        if self.cfg.get("_did_auto_import", False):
            return
        found = import_chromium_bookmarks()
        if found:
            existing_urls = {b.get("url") for b in self.cfg.get("bookmarks", [])}
            for it in found:
                if it.get("url") not in existing_urls:
                    self.cfg["bookmarks"].append(it)
            save_config(self.cfg)
            self.refresh_bookmarks_toolbar()
        self.cfg["_did_auto_import"] = True
        save_config(self.cfg)

    # ========== Session ==========
    def save_session(self):
        tabs = []
        for i in range(self.tabs.count()):
            w = self.tabs.widget(i)
            if isinstance(w, QWebEngineView):
                tabs.append(w.url().toString())
            else:
                tabs.append(QUrl.fromLocalFile(os.path.abspath(STARTPAGE_FILE)).toString())
        self.cfg["session"] = {
            "tabs": tabs,
            "current_index": max(0, self.tabs.currentIndex())
        }
        save_config(self.cfg)

    def restore_session(self):
        sess = self.cfg.get("session", {})
        tabs = sess.get("tabs", [])
        if not tabs:
            self.open_start_in_new_tab(); return
        self.tabs.clear()
        for url in tabs:
            self.create_tab(url)
        idx = min(sess.get("current_index", 0), self.tabs.count()-1)
        if idx >= 0:
            self.tabs.setCurrentIndex(idx)

    # ========== Helperi TAB-uri ==========
    def create_view(self, url: str):
        profile = QWebEngineProfile.defaultProfile()
        page = MyWebEnginePage(profile, self)
        view = QWebEngineView()
        view.setPage(page)
        try:
            view.page().profile().setHttpUserAgent(UA_CHROME)
        except Exception as e:
            print("NU am putut seta User-Agent:", e)
        s = view.settings()
        s.setAttribute(QWebEngineSettings.JavascriptEnabled, True)
        s.setAttribute(QWebEngineSettings.LocalStorageEnabled, True)
        s.setAttribute(QWebEngineSettings.PluginsEnabled, True)
        s.setAttribute(QWebEngineSettings.JavascriptCanOpenWindows, True)
        s.setAttribute(QWebEngineSettings.JavascriptCanAccessClipboard, True)
        s.setAttribute(QWebEngineSettings.LocalContentCanAccessRemoteUrls, True)
        view.setZoomFactor(float(self.cfg.get("zoom", 1.0)))
        view.urlChanged.connect(self.sync_urlbar)
        view.loadFinished.connect(lambda ok, v=view: self.on_load_finished(ok, v))
        page.popupRequested.connect(self.on_popup_requested)
        view.setUrl(QUrl(url))
        return view

    def on_popup_requested(self, url: QUrl):
        host = url.host().lower()
        feats = self.cfg.get("features", {})
        allow_all = not feats.get("block_popups", True)
        ask = feats.get("ask_on_popup", True)
        wl = [h.lower() for h in self.cfg.get("popup_whitelist", [])]
        bl = [h.lower() for h in self.cfg.get("popup_blacklist", [])]
        if allow_all or host in wl:
            self.create_tab(url.toString())
            return
        if host in bl:
            return
        if ask:
            dlg = PopupDialog(host, self)
            size = dlg.sizeHint()
            geo = self.geometry()
            dlg.move(geo.x() + geo.width() - size.width() - 20, geo.y() + 20)
            dlg.exec_()
            decision = dlg.decision
            if decision == "allow":
                self.create_tab(url.toString())
            elif decision == "allow_all":
                self.cfg.setdefault("popup_whitelist", []).append(host)
                save_config(self.cfg)
                self.create_tab(url.toString())
            elif decision == "deny_all":
                self.cfg.setdefault("popup_blacklist", []).append(host)
                save_config(self.cfg)
            else:
                pass

    def create_tab(self, url: str):
        view = self.create_view(url)
        idx = self.tabs.addTab(view, "Tab")
        self.tabs.setCurrentIndex(idx)
        return idx

    def open_start_in_new_tab(self):
        url = QUrl.fromLocalFile(os.path.abspath(STARTPAGE_FILE)).toString()
        self.create_tab(url)

    def show_tab_list(self):
        menu = QMenu(self)
        for i in range(self.tabs.count()):
            title = self.tabs.tabText(i) or f"Tab {i+1}"
            act = QAction(title, self)
            act.triggered.connect(lambda _, ix=i: self.tabs.setCurrentIndex(ix))
            menu.addAction(act)
        menu.exec_(self.list_tabs_btn.mapToGlobal(self.list_tabs_btn.rect().bottomRight()))

    def close_tab(self, index):
        if self.tabs.count() > 1:
            widget = self.tabs.widget(index)
            self.tabs.removeTab(index)
            widget.deleteLater()
        else:
            self.open_start_in_new_tab()

    def pin_tab(self, index):
        data = self.tabs.tabData(index) or {}
        pinned = data.get("pinned", False)
        if pinned:
            data["pinned"] = False
            self.tabs.setTabData(index, data)
            pinned_count = sum(
                1
                for i in range(self.tabs.count())
                if self.tabs.tabData(i) and self.tabs.tabData(i).get("pinned")
            )
            self.tabs.tabBar().moveTab(index, pinned_count)
        else:
            data["pinned"] = True
            self.tabs.setTabData(index, data)
            pinned_count = sum(
                1
                for i in range(self.tabs.count())
                if self.tabs.tabData(i) and self.tabs.tabData(i).get("pinned")
            )
            self.tabs.tabBar().moveTab(index, max(0, pinned_count - 1))

    def duplicate_tab(self, index):
        w = self.tabs.widget(index)
        if isinstance(w, QWebEngineView):
            url = w.url().toString()
            view = self.create_view(url)
            self.tabs.insertTab(index + 1, view, self.tabs.tabText(index))
            self.tabs.setCurrentIndex(index + 1)

    def close_other_tabs(self, index):
        for i in reversed(range(self.tabs.count())):
            if i != index and not (self.tabs.tabData(i) and self.tabs.tabData(i).get("pinned")):
                self.close_tab(i)

    def on_tab_context_menu(self, pos):
        bar = self.tabs.tabBar()
        index = bar.tabAt(pos)
        if index < 0:
            return
        data = self.tabs.tabData(index) or {}
        pinned = data.get("pinned", False)
        menu = QMenu(self)
        pin_act = QAction("Unpin" if pinned else "Pin", self)
        pin_act.triggered.connect(lambda: self.pin_tab(index))
        dup_act = QAction("Duplicate", self)
        dup_act.triggered.connect(lambda: self.duplicate_tab(index))
        close_others_act = QAction("Close others", self)
        close_others_act.triggered.connect(lambda: self.close_other_tabs(index))
        menu.addAction(pin_act)
        menu.addAction(dup_act)
        menu.addAction(close_others_act)
        menu.exec_(bar.mapToGlobal(pos))

    def current_view(self) -> QWebEngineView:
        w = self.tabs.currentWidget()
        return w if isinstance(w, QWebEngineView) else None

    def on_tab_changed(self, index):
        view = self.current_view()
        if view:
            self.sync_urlbar(view.url())
        self.update_adblock_action()

    # ========== Navigare ==========
    def navigate(self):
        text = self.url_bar.text().strip()
        if not text:
            return
        if not (text.startswith("http://") or text.startswith("https://")):
            text = f"https://www.google.com/search?q={text.replace(' ', '+')}"
        v = self.current_view()
        if v:
            v.setUrl(QUrl(text))

    def go_home(self):
        v = self.current_view()
        if v:
            v.setUrl(QUrl(self.cfg.get("home_url", DEFAULT_CONFIG["home_url"])))

    def sync_urlbar(self, url):
        self.url_bar.setText(url.toString())
        v = self.current_view()
        if v and v.url().toString() == url.toString():
            title = v.title() or domain_from_url(url.toString()) or "Tab"
            self.tabs.setTabText(self.tabs.currentIndex(), title[:24])

    def on_load_finished(self, ok, view):
        if not ok:
            QMessageBox.warning(self, "Eroare", "Pagina nu a putut fi încărcată.")
        else:
            if view == self.current_view():
                self.tabs.setTabText(self.tabs.currentIndex(), view.title() or "(fără titlu)")
            if self.cfg.get("features", {}).get("yt_autoskip", True):
                try:
                    u = view.url().toString().lower()
                    if "youtube.com" in u:
                        js = """
                        try{
                          setInterval(function(){
                            var btn=document.querySelector('.ytp-ad-skip-button');
                            if(btn){ btn.click(); }
                            var closeAd=document.querySelector('.ytp-ad-overlay-close-button');
                            if(closeAd){ closeAd.click(); }
                          }, 500);
                        }catch(e){}
                        """
                        view.page().runJavaScript(js)
                except Exception:
                    pass

    # ========== Zoom ==========
    def zoom_in(self):
        v = self.current_view()
        if not v:
            return
        z = min(v.zoomFactor() + 0.1, 3.0)
        v.setZoomFactor(z)
        self.cfg["zoom"] = float(z)
        save_config(self.cfg)

    def zoom_out(self):
        v = self.current_view()
        if not v:
            return
        z = max(v.zoomFactor() - 0.1, 0.5)
        v.setZoomFactor(z)
        self.cfg["zoom"] = float(z)
        save_config(self.cfg)

    def zoom_reset(self):
        v = self.current_view()
        if not v:
            return
        v.setZoomFactor(1.0)
        self.cfg["zoom"] = 1.0
        save_config(self.cfg)

    # ========== Bookmarks ==========
    def refresh_bookmarks_toolbar(self):
        self.bm_toolbar.clear()
        font = self.bm_toolbar.font()
        font.setPointSize(self.cfg.get("bookmark_font_px", 14))
        self.bm_toolbar.setFont(font)
        for bm in self.cfg.get("bookmarks", []):
            act = QAction(bm.get("title", "Link"), self)
            act.triggered.connect(lambda _, u=bm.get("url", ""): self.open_in_current_tab(u))
            self.bm_toolbar.addAction(act)
        manage = QAction("✎", self)
        manage.setToolTip("Gestionează bookmarks")
        manage.triggered.connect(self.manage_bookmarks)
        self.bm_toolbar.addSeparator()
        self.bm_toolbar.addAction(manage)

    def open_in_current_tab(self, url):
        v = self.current_view()
        if v:
            v.setUrl(QUrl(url))

    def add_bookmark(self):
        title, ok = QInputDialog.getText(self, "Titlu bookmark", "Titlu:")
        if not ok or not title:
            return
        v = self.current_view()
        url = v.url().toString() if v else self.cfg.get("home_url")
        bm = {"title": title, "url": url}
        lst = self.cfg.get("bookmarks", [])
        lst.append(bm)
        self.cfg["bookmarks"] = lst
        save_config(self.cfg)
        self.refresh_bookmarks_toolbar()

    def manage_bookmarks(self):
        choice = QMessageBox.question(
            self, "Bookmarks",
            "Exportezi (Da) sau imporți (Nu) bookmarks?",
            QMessageBox.Yes | QMessageBox.No | QMessageBox.Cancel,
            QMessageBox.Cancel
        )
        if choice == QMessageBox.Yes:
            path, _ = QFileDialog.getSaveFileName(self, "Export bookmarks", "bookmarks.json", "JSON (*.json)")
            if path:
                try:
                    with open(path, "w", encoding="utf-8") as f:
                        json.dump(self.cfg.get("bookmarks", []), f, indent=2, ensure_ascii=False)
                except Exception as e:
                    QMessageBox.critical(self, "Eroare", str(e))
        elif choice == QMessageBox.No:
            path, _ = QFileDialog.getOpenFileName(self, "Import bookmarks", "", "JSON (*.json)")
            if path:
                try:
                    with open(path, "r", encoding="utf-8") as f:
                        data = json.load(f)
                    if isinstance(data, list):
                        self.cfg["bookmarks"] = data
                        save_config(self.cfg)
                        self.refresh_bookmarks_toolbar()
                    else:
                        QMessageBox.warning(self, "Format invalid", "JSON-ul trebuie să fie o listă de obiecte {title, url}.")
                except Exception as e:
                    QMessageBox.critical(self, "Eroare", str(e))

    # ========== Adblock ==========
    def toggle_adblock_site(self):
        v = self.current_view()
        if not v:
            return
        host = v.url().host().lower()
        wl = self.cfg.setdefault("adblock_whitelist", [])
        if host in wl:
            wl.remove(host)
            self.cfg["popup_whitelist"] = [h for h in self.cfg.get("popup_whitelist", []) if h != host]
            self.cfg["popup_blacklist"] = [h for h in self.cfg.get("popup_blacklist", []) if h != host]
            msg = f"AdBlock activat pentru {host}"
        else:
            wl.append(host)
            msg = f"AdBlock dezactivat pentru {host}"
        save_config(self.cfg)
        box = QMessageBox(self)
        box.setWindowTitle("AdBlock")
        box.setText(msg)
        box.setStandardButtons(QMessageBox.Ok)
        size = box.sizeHint()
        geo = self.geometry()
        box.move(geo.x() + geo.width() - size.width() - 20, geo.y() + 20)
        box.exec_()
        self.update_adblock_action()
        v.reload()

    def update_adblock_action(self):
        v = self.current_view()
        host = v.url().host().lower() if v else ""
        wl = [h.lower() for h in self.cfg.get("adblock_whitelist", [])]
        if host in wl:
            self.adblock_act.setText("🚫")
            self.adblock_act.setToolTip("AdBlock dezactivat - clic pentru activare")
        else:
            self.adblock_act.setText("⛔")
            self.adblock_act.setToolTip("AdBlock activ - clic pentru dezactivare pe acest site")

    # ========== Settings Tab ==========
    def open_settings_tab(self):
        tab = SettingsTab(self, self.cfg, self.on_import_bookmarks_click)
        idx = self.tabs.addTab(tab, "Setări")
        self.tabs.setCurrentIndex(idx)

    def on_import_bookmarks_click(self):
        found = import_chromium_bookmarks()
        if not found:
            QMessageBox.information(self, "Import", "Nu am găsit bookmarks în Chrome/Edge/Brave sau fișierul lipsește.")
            return
        existing_urls = {b.get("url") for b in self.cfg.get("bookmarks", [])}
        added = 0
        for it in found:
            if it.get("url") not in existing_urls:
                self.cfg["bookmarks"].append(it)
                existing_urls.add(it.get("url"))
                added += 1
        save_config(self.cfg)
        self.refresh_bookmarks_toolbar()
        QMessageBox.information(self, "Import", f"Import finalizat. Adăugate {added} bookmark-uri noi.")

    # ========== UI / Temă ==========
    def apply_fonts(self):
        ui_px = int(self.cfg.get("ui_font_px", 14))
        app_font = QApplication.font()
        app_font.setPointSize(ui_px)
        QApplication.instance().setFont(app_font)

    def update_stylesheet(self):
        accent = self.cfg.get("accent_color", DEFAULT_CONFIG["accent_color"])
        acc = QColor(accent)
        tab_sel_bg = f"rgba({acc.red()}, {acc.green()}, {acc.blue()}, 0.18)"
        self.setStyleSheet(f"""
            QMainWindow {{ background-color: #0b0b0d; }}
            QToolBar {{
              background: rgba(255,255,255,0.04);
              border: 1px solid rgba(255,255,255,0.08);
              padding: 8px; spacing: 8px; border-radius: 14px;
            }}
            QLineEdit {{
              background: rgba(255,255,255,0.06); color: #eaeaea; border: 1px solid rgba(255,255,255,0.1);
              padding: 12px 14px; border-radius: 12px; min-width: 400px;
            }}
            QLineEdit:focus {{ border-color: {accent}; }}
            QTabBar::tab {{
              background: rgba(255,255,255,0.05); color: #d9d9d9; padding: 10px 14px; border-radius: 10px; margin: 6px 6px 2px 6px;
            }}
            QTabBar::tab:selected {{ background: {tab_sel_bg}; color: #ffffff; }}
            QToolButton {{ color: #f0f0f0; }}
            QToolButton:hover {{ color: #ffffff; }}
        """)

    def apply_theme(self, dark: bool):
        self.cfg["dark_mode"] = bool(dark)
        save_config(self.cfg)
        pal = QPalette()
        if dark:
            pal.setColor(QPalette.Window, QColor(11, 11, 13))
            pal.setColor(QPalette.WindowText, QColor(235, 235, 235))
            pal.setColor(QPalette.Base, QColor(24, 24, 28))
            pal.setColor(QPalette.AlternateBase, QColor(20, 20, 24))
            pal.setColor(QPalette.ToolTipBase, QColor(40, 40, 44))
            pal.setColor(QPalette.ToolTipText, QColor(240, 240, 240))
            pal.setColor(QPalette.Text, QColor(230, 230, 230))
            pal.setColor(QPalette.Button, QColor(26, 26, 30))
            pal.setColor(QPalette.ButtonText, QColor(230, 230, 230))
            pal.setColor(QPalette.BrightText, Qt.red)
            pal.setColor(QPalette.Highlight, QColor(self.cfg.get("accent_color", DEFAULT_CONFIG["accent_color"])))
            pal.setColor(QPalette.HighlightedText, Qt.white)
        else:
            pal = QApplication.style().standardPalette()
        QApplication.instance().setPalette(pal)

    def toggle_dark_mode(self):
        self.apply_theme(not self.cfg.get("dark_mode", True))

    def open_settings(self):
        pass

    # ====== lifecycle ======
    def closeEvent(self, event):
        if self.cfg.get("persist_tabs", True):
            self.save_session()
        return super().closeEvent(event)


def main():
    cfg = load_config()
    app = QApplication(sys.argv)

    # UA global backup
    try:
        profile = QWebEngineProfile.defaultProfile()
        profile.setHttpUserAgent(UA_CHROME)
    except Exception as e:
        print("NU am putut seta UA global:", e)

    # Font global la start
    f = QFont()
    f.setPointSize(int(cfg.get("ui_font_px", 14)))
    app.setFont(f)

    w = Browser(cfg)
    w.show()
    sys.exit(app.exec_())


if __name__ == "__main__":
    main()
