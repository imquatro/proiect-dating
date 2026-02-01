import tkinter as tk
import time
import json
import threading
import sys
import os
import colorsys
import webbrowser
import math
import traceback
import importlib.util
import ctypes
from ctypes import wintypes
try:
    import winsound
except ImportError:
    winsound = None
from pynput import mouse, keyboard

# =========================
#  Hide console (Windows)
# =========================
if sys.platform == "win32":
    try:
        import ctypes
        hwnd = ctypes.windll.kernel32.GetConsoleWindow()
        if hwnd:
            ctypes.windll.user32.ShowWindow(hwnd, 0)
    except Exception:
        pass

# =========================
#  Macro recorder
# =========================
FILENAME = "catapulte_clicks.json"
SETTINGS_FILE = "im_quatro_settings.json"

click_positions = []
recording = False
mouse_controller = mouse.Controller()

STEAM_URL = "https://steamcommunity.com/profiles/76561199438588468/"
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
SPLASH_IMAGE = os.path.join(SCRIPT_DIR, "quatrosss.png")
SOUND_HUD_OPENING = os.path.join(SCRIPT_DIR, "hudopening.mp3")
SOUND_STARTED = os.path.join(SCRIPT_DIR, "started.mp3")
LOG_FILE = os.path.join(SCRIPT_DIR, "im_quatro_debug.log")


def log_to_file(message):
    try:
        stamp = time.strftime("%Y-%m-%d %H:%M:%S")
        with open(LOG_FILE, "a", encoding="utf-8") as f:
            f.write(f"[{stamp}] {message}\n")
    except Exception:
        pass

user32 = ctypes.WinDLL("user32", use_last_error=True)
INPUT_MOUSE = 0
INPUT_KEYBOARD = 1
MOUSEEVENTF_MOVE = 0x0001
MOUSEEVENTF_LEFTDOWN = 0x0002
MOUSEEVENTF_LEFTUP = 0x0004
MOUSEEVENTF_ABSOLUTE = 0x8000


class MOUSEINPUT(ctypes.Structure):
    _fields_ = [
        ("dx", wintypes.LONG),
        ("dy", wintypes.LONG),
        ("mouseData", wintypes.DWORD),
        ("dwFlags", wintypes.DWORD),
        ("time", wintypes.DWORD),
        ("dwExtraInfo", wintypes.ULONG_PTR),
    ]


class KEYBDINPUT(ctypes.Structure):
    _fields_ = [
        ("wVk", wintypes.WORD),
        ("wScan", wintypes.WORD),
        ("dwFlags", wintypes.DWORD),
        ("time", wintypes.DWORD),
        ("dwExtraInfo", wintypes.ULONG_PTR),
    ]


class _INPUTUNION(ctypes.Union):
    _fields_ = [("mi", MOUSEINPUT), ("ki", KEYBDINPUT)]


class INPUT(ctypes.Structure):
    _fields_ = [("type", wintypes.DWORD), ("union", _INPUTUNION)]


def _send_input(*inputs):
    n = len(inputs)
    arr = (INPUT * n)(*inputs)
    cb = ctypes.sizeof(INPUT)
    sent = user32.SendInput(n, arr, cb)
    if sent != n:
        raise ctypes.WinError(ctypes.get_last_error())


def left_click_native():
    _send_input(
        INPUT(type=INPUT_MOUSE, union=_INPUTUNION(mi=MOUSEINPUT(0, 0, 0, MOUSEEVENTF_LEFTDOWN, 0, 0))),
        INPUT(type=INPUT_MOUSE, union=_INPUTUNION(mi=MOUSEINPUT(0, 0, 0, MOUSEEVENTF_LEFTUP, 0, 0))),
    )


def move_mouse_abs(x, y):
    screen_w = user32.GetSystemMetrics(0) - 1
    screen_h = user32.GetSystemMetrics(1) - 1
    nx = int(x * 65535 / screen_w)
    ny = int(y * 65535 / screen_h)
    _send_input(
        INPUT(type=INPUT_MOUSE, union=_INPUTUNION(mi=MOUSEINPUT(nx, ny, 0, MOUSEEVENTF_MOVE | MOUSEEVENTF_ABSOLUTE, 0, 0)))
    )


def click_at(x, y, delay=0.01):
    move_mouse_abs(x, y)
    time.sleep(delay)
    left_click_native()


def save_clicks():
    with open(FILENAME, "w", encoding="utf-8") as f:
        json.dump(click_positions, f)


def load_clicks():
    with open(FILENAME, "r", encoding="utf-8") as f:
        return json.load(f)


def clamp(v, a, b):
    return max(a, min(b, v))


def rgb_to_hex(r, g, b):
    return "#{:02x}{:02x}{:02x}".format(int(r), int(g), int(b))


def hex_to_rgb(h):
    h = h.lstrip("#")
    return int(h[0:2], 16), int(h[2:4], 16), int(h[4:6], 16)


# =========================
#  Settings persistence (NO THEME COLORS)
# =========================
def key_to_store(k):
    # store as string for json
    try:
        if isinstance(k, keyboard.Key):
            return f"KEY:{str(k).replace('Key.', '')}"  # e.g. KEY:f8
        if isinstance(k, keyboard.KeyCode):
            # num0
            if k.vk == 96:
                return "NUM0"
            if k.char:
                return f"CHAR:{k.char}"
            if k.vk is not None:
                return f"VK:{k.vk}"
    except Exception:
        pass
    return "UNKNOWN"


def store_to_key(s):
    # parse stored string back to pynput key
    try:
        if s == "NUM0":
            return keyboard.KeyCode.from_vk(96)
        if s.startswith("KEY:"):
            name = s.split(":", 1)[1].strip().lower()
            return getattr(keyboard.Key, name)
        if s.startswith("CHAR:"):
            ch = s.split(":", 1)[1]
            return keyboard.KeyCode.from_char(ch)
        if s.startswith("VK:"):
            vk = int(s.split(":", 1)[1])
            return keyboard.KeyCode.from_vk(vk)
    except Exception:
        pass
    # fallback
    return keyboard.KeyCode.from_char('`')


def default_settings():
    return {
        "delay_ms": 200,
        "xbutton_delay_ms": 60,
        "mbutton_delay_ms": 120,
        "grid_margin": [10, 10],
        "grid_spacing": [40, 40],
        "grid_angle_deg": 0.0,
        "grid_ready": False,
        "relative": True,
        "active_tab": "Commands",
        "hotkeys": {
            "start": "CHAR:[",
            "stop": "CHAR:]",
            "play": "CHAR:`",
            "xbutton_toggle": "KEY:f6",
            "mbutton_sequence": "KEY:f7",
            "quit": "NUM0",
            "toggle_topmost": "KEY:f10",
            "toggle_visible": "KEY:f11",
        },
        "menu_pos": [140, 120],
        "eye_pos": [20, 20],
    }


def load_settings_file():
    if not os.path.exists(SETTINGS_FILE):
        return default_settings()
    try:
        with open(SETTINGS_FILE, "r", encoding="utf-8") as f:
            data = json.load(f)
        # merge defaults for missing keys
        d = default_settings()
        d.update({k: data.get(k, d[k]) for k in d.keys()})
        # hotkeys merge
        hk = d["hotkeys"].copy()
        if isinstance(data.get("hotkeys"), dict):
            hk.update(data["hotkeys"])
        d["hotkeys"] = hk
        # ensure types
        if not isinstance(d.get("menu_pos"), list) or len(d["menu_pos"]) != 2:
            d["menu_pos"] = default_settings()["menu_pos"]
        if not isinstance(d.get("eye_pos"), list) or len(d["eye_pos"]) != 2:
            d["eye_pos"] = default_settings()["eye_pos"]
        if d.get("active_tab") not in ("Commands", "Theme", "About me"):
            d["active_tab"] = "Commands"
        d["delay_ms"] = int(d.get("delay_ms", 200))
        d["xbutton_delay_ms"] = int(d.get("xbutton_delay_ms", 60))
        d["mbutton_delay_ms"] = int(d.get("mbutton_delay_ms", 120))
        d["grid_margin"] = data.get("grid_margin", d.get("grid_margin", [10, 10]))
        d["grid_spacing"] = data.get("grid_spacing", d.get("grid_spacing", [40, 40]))
        d["grid_angle_deg"] = float(d.get("grid_angle_deg", 0.0))
        d["grid_ready"] = bool(d.get("grid_ready", False))
        d["relative"] = bool(d.get("relative", True))
        return d
    except Exception:
        return default_settings()


def save_settings_file(payload):
    try:
        with open(SETTINGS_FILE, "w", encoding="utf-8") as f:
            json.dump(payload, f, indent=2)
    except Exception:
        pass


# =========================
#  ImGui-like App (Canvas)
# =========================
class ImGuiApp:
    def __init__(self):
        log_to_file("App init start.")
        self.settings = load_settings_file()
        log_to_file("Settings loaded.")

        self.root = tk.Tk()
        log_to_file("Tk root created.")
        self.root.title("IM QUATRO MACROS")
        self.root.geometry("820x460")
        self.root.minsize(820, 460)
        self.root.overrideredirect(True)

        # Always on top so it doesn't "disappear"
        self.topmost = True
        self.root.attributes("-topmost", True)

        # restore window pos
        try:
            mx, my = self.settings["menu_pos"]
            self.root.geometry(f"+{int(mx)}+{int(my)}")
        except Exception:
            pass

        self.canvas = tk.Canvas(self.root, highlightthickness=0, bd=0)
        self.canvas.pack(fill="both", expand=True)

        self.splash = None
        self.splash_image = None
        self.splash_duration_ms = 5000
        self.splash_steps = 50
        self.splash_step_ms = int(self.splash_duration_ms / self.splash_steps)

        # ----- state (theme NOT persisted) -----
        self.state = {
            "bg": "#1b1f24",
            "title": "#111418",
            "panel": "#151a20",
            "accent": "#2d78ff",
            "text": "#e6e6e6",
            "muted": "#9aa3ad",
            "alpha": 0.96,

            # Delay in ms
            "delay_ms": int(self.settings.get("delay_ms", 200)),
            "xbutton_delay_ms": int(self.settings.get("xbutton_delay_ms", 60)),
            "mbutton_delay_ms": int(self.settings.get("mbutton_delay_ms", 120)),
            "delay_max": 1500,

            # Modes
            "relative": bool(self.settings.get("relative", True)),

            "grid_margin_x": int(self.settings.get("grid_margin", [10, 10])[0]),
            "grid_margin_y": int(self.settings.get("grid_margin", [10, 10])[1]),
            "grid_spacing_x": int(self.settings.get("grid_spacing", [40, 40])[0]),
            "grid_spacing_y": int(self.settings.get("grid_spacing", [40, 40])[1]),
            "grid_angle_deg": float(self.settings.get("grid_angle_deg", 0.0)),
            "grid_ready": bool(self.settings.get("grid_ready", False)),
        }
        self.root.attributes("-alpha", self.state["alpha"])

        # Tabs
        self.active_tab = self.settings.get("active_tab", "Commands")
        if self.active_tab not in ("Commands", "Theme", "About me"):
            self.active_tab = "Commands"

        # Hotkeys (persisted)
        self.hotkeys = {
            "start": store_to_key(self.settings["hotkeys"].get("start", "CHAR:[")),
            "stop": store_to_key(self.settings["hotkeys"].get("stop", "CHAR:]")),
            "play": store_to_key(self.settings["hotkeys"].get("play", "CHAR:`")),
            "xbutton_toggle": store_to_key(self.settings["hotkeys"].get("xbutton_toggle", "KEY:f6")),
            "mbutton_sequence": store_to_key(self.settings["hotkeys"].get("mbutton_sequence", "KEY:f7")),
            "quit": store_to_key(self.settings["hotkeys"].get("quit", "NUM0")),
            "toggle_topmost": store_to_key(self.settings["hotkeys"].get("toggle_topmost", "KEY:f10")),
            "toggle_visible": store_to_key(self.settings["hotkeys"].get("toggle_visible", "KEY:f11")),
        }

        # UI layout
        self.pad = 14
        self.title_h = 34
        self.left_w = 270

        self.drag = {"active": False, "x": 0, "y": 0}
        self.hit = {}
        self.hover = None
        self.active_slider = None
        self.slider_map = {}

        # Logs
        self.logs = []
        self.log_limit = 200
        self.log_scroll_offset = 0
        self.max_logs = 6
        self.slider_labels = {
            "delay_ms": "Delay",
            "xbutton_delay_ms": "Autoclicker delay",
            "mbutton_delay_ms": "Grups moving delay",
        }
        self.last_slider_values = {
            "delay_ms": self.state["delay_ms"],
            "xbutton_delay_ms": self.state["xbutton_delay_ms"],
            "mbutton_delay_ms": self.state["mbutton_delay_ms"],
        }
        self.button_labels = {}

        # Color picker docked
        self.color_dock = None

        # Keybind edit overlay
        self.bind_edit = None

        # Delay Entry (real Tk widget)
        self.delay_var = tk.StringVar(value=str(self.state["delay_ms"]))
        self.delay_entry = tk.Entry(
            self.root,
            textvariable=self.delay_var,
            width=8,
            bg="#0f1216",
            fg="white",
            insertbackground="white",
            relief="flat"
        )
        self.delay_entry.bind("<Return>", self.apply_delay_entry)
        self.delay_entry.bind("<FocusOut>", self.apply_delay_entry)

        self.xbutton_delay_var = tk.StringVar(value=str(self.state["xbutton_delay_ms"]))
        self.xbutton_delay_entry = tk.Entry(
            self.root,
            textvariable=self.xbutton_delay_var,
            width=8,
            bg="#0f1216",
            fg="white",
            insertbackground="white",
            relief="flat"
        )
        self.xbutton_delay_entry.bind("<Return>", self.apply_xbutton_delay_entry)
        self.xbutton_delay_entry.bind("<FocusOut>", self.apply_xbutton_delay_entry)

        self.mbutton_delay_var = tk.StringVar(value=str(self.state["mbutton_delay_ms"]))
        self.mbutton_delay_entry = tk.Entry(
            self.root,
            textvariable=self.mbutton_delay_var,
            width=8,
            bg="#0f1216",
            fg="white",
            insertbackground="white",
            relief="flat"
        )
        self.mbutton_delay_entry.bind("<Return>", self.apply_mbutton_delay_entry)
        self.mbutton_delay_entry.bind("<FocusOut>", self.apply_mbutton_delay_entry)

        # Playback control
        self.play_running = False
        self.play_stop_event = threading.Event()

        # Autoclicker control
        self.xbutton_stop_event = threading.Event()
        self.xbutton_thread = None

        # MButton sequence control
        self.mbutton_running = threading.Event()
        self.mbutton_stop_event = threading.Event()
        self.keyboard_controller = keyboard.Controller()

        # Eye widget (draggable)
        self.eye = tk.Toplevel(self.root)
        self.eye.overrideredirect(True)
        self.eye.attributes("-topmost", True)
        self.eye.attributes("-alpha", self.state["alpha"])
        self.eye_canvas = tk.Canvas(self.eye, width=34, height=34, highlightthickness=0, bd=0, bg=self.state["title"])
        self.eye_canvas.pack(fill="both", expand=True)
        self.eye_canvas.create_text(17, 17, text="👁", fill=self.state["text"], font=("Segoe UI", 14, "bold"))
        self.eye.withdraw()

        self.eye_drag = {"active": False, "x": 0, "y": 0}
        try:
            ex, ey = self.settings["eye_pos"]
            self.eye.geometry(f"+{int(ex)}+{int(ey)}")
        except Exception:
            self.eye.geometry("+20+20")

        self.eye_canvas.bind("<Button-1>", self.on_eye_down)
        self.eye_canvas.bind("<ButtonRelease-1>", self.on_eye_up)
        self.eye_canvas.bind("<B1-Motion>", self.on_eye_drag)
        self.eye_canvas.bind("<Double-Button-1>", lambda e: self.show_menu())

        # Mouse listener
        self.mouse_listener = mouse.Listener(
            on_click=self.on_click,
            on_move=self.on_move_global,
            on_scroll=self.on_scroll_global,
        )
        self.mouse_listener.start()
        log_to_file("Mouse listener started.")

        # Canvas events
        self.canvas.bind("<Motion>", self.on_motion)
        self.canvas.bind("<Button-1>", self.on_down)
        self.canvas.bind("<ButtonRelease-1>", self.on_up)
        self.canvas.bind("<B1-Motion>", self.on_drag)
        self.canvas.bind("<Configure>", lambda e: self.draw())

        self.root.bind("<Escape>", lambda e: self.quit())

        # Global keyboard listener
        self.k_listener = keyboard.Listener(
            on_press=self.on_key_press_global,
            on_release=self.on_key_release_global,
        )
        self.k_listener.start()
        log_to_file("Keyboard listener started.")

        self.shift_down = False
        self.ctrl_down = False
        self.grid_mode = None
        self.grid_rect = None
        self.grid_drag_active = False
        self.grid_drag_kind = None
        self.grid_drag_start = None
        self.grid_calibration_points = []
        self.rotation_mode = False
        self.rotation_rect = None
        self.rotation_visible = False
        self.last_rotation_time = 0.0

        self.root.after(200, self.check_rotation_hide)

        self.root.withdraw()
        log_to_file("Splash start.")
        self.show_splash()
        log_to_file("Main loop enter.")
        self.root.mainloop()

    # =========================
    # Persistence
    # =========================
    def persist(self):
        payload = default_settings()
        payload["delay_ms"] = int(self.state["delay_ms"])
        payload["xbutton_delay_ms"] = int(self.state["xbutton_delay_ms"])
        payload["mbutton_delay_ms"] = int(self.state["mbutton_delay_ms"])
        payload["relative"] = bool(self.state["relative"])
        payload["active_tab"] = self.active_tab
        payload["grid_margin"] = [int(self.state["grid_margin_x"]), int(self.state["grid_margin_y"])]
        payload["grid_spacing"] = [int(self.state["grid_spacing_x"]), int(self.state["grid_spacing_y"])]
        payload["grid_angle_deg"] = float(self.state["grid_angle_deg"])
        payload["grid_ready"] = bool(self.state["grid_ready"])

        # positions
        try:
            payload["menu_pos"] = [int(self.root.winfo_x()), int(self.root.winfo_y())]
        except Exception:
            pass
        try:
            payload["eye_pos"] = [int(self.eye.winfo_x()), int(self.eye.winfo_y())]
        except Exception:
            pass

        # hotkeys
        hk = {}
        for k, v in self.hotkeys.items():
            hk[k] = key_to_store(v)
        payload["hotkeys"] = hk

        save_settings_file(payload)

    # =========================
    # Logging
    # =========================
    def log(self, msg):
        was_at_bottom = self.log_scroll_offset == 0
        self.logs.append(msg)
        if len(self.logs) > self.log_limit:
            overflow = len(self.logs) - self.log_limit
            self.logs = self.logs[overflow:]
            if self.log_scroll_offset > 0:
                self.log_scroll_offset = max(0, self.log_scroll_offset - overflow)
        if was_at_bottom:
            self.log_scroll_offset = 0
        self.draw()

    def screen_to_canvas(self, x, y):
        return x - self.root.winfo_rootx(), y - self.root.winfo_rooty()

    def normalize_rect(self, rect):
        x1, y1, x2, y2 = rect
        return min(x1, x2), min(y1, y2), max(x1, x2), max(y1, y2)

    def point_in_rect(self, x, y, rect):
        x1, y1, x2, y2 = self.normalize_rect(rect)
        return x1 <= x <= x2 and y1 <= y <= y2

    def rotate_point(self, x, y, cx, cy, angle_deg):
        if not angle_deg:
            return x, y
        ang = math.radians(angle_deg)
        cos_a = math.cos(ang)
        sin_a = math.sin(ang)
        tx = x - cx
        ty = y - cy
        rx = tx * cos_a - ty * sin_a
        ry = tx * sin_a + ty * cos_a
        return cx + rx, cy + ry

    def compute_grid_points(self, rect):
        x1, y1, x2, y2 = self.normalize_rect(rect)
        margin_x = max(0, int(self.state["grid_margin_x"]))
        margin_y = max(0, int(self.state["grid_margin_y"]))
        spacing_x = max(1, int(self.state["grid_spacing_x"]))
        spacing_y = max(1, int(self.state["grid_spacing_y"]))
        start_x = x1 + margin_x
        start_y = y1 + margin_y
        end_x = x2 - margin_x
        end_y = y2 - margin_y
        if end_x < start_x or end_y < start_y:
            return []
        cols = int((end_x - start_x) // spacing_x) + 1
        rows = int((end_y - start_y) // spacing_y) + 1
        cx = (x1 + x2) / 2
        cy = (y1 + y2) / 2
        points = []
        for row in range(rows):
            for col in range(cols):
                px = start_x + col * spacing_x
                py = start_y + row * spacing_y
                rx, ry = self.rotate_point(px, py, cx, cy, self.state["grid_angle_deg"])
                points.append((rx, ry))
        return points

    def play_sound_async(self, sound_path, duration=None):
        if not os.path.exists(sound_path):
            return
        threading.Thread(target=self._play_sound, args=(sound_path, duration), daemon=True).start()

    def _play_sound(self, sound_path, duration):
        if importlib.util.find_spec("pygame"):
            pygame = importlib.import_module("pygame")
            if not pygame.mixer.get_init():
                try:
                    pygame.mixer.init()
                except Exception:
                    return
            try:
                sound = pygame.mixer.Sound(sound_path)
                sound.play()
                if duration:
                    time.sleep(duration)
            except Exception:
                return
            return
        if importlib.util.find_spec("playsound"):
            playsound_mod = importlib.import_module("playsound")
            try:
                playsound_mod.playsound(sound_path)
            except Exception:
                return
            return
        if sys.platform == "win32":
            if winsound and sound_path.lower().endswith(".wav"):
                try:
                    winsound.PlaySound(sound_path, winsound.SND_FILENAME | winsound.SND_ASYNC)
                except Exception:
                    return
            return

    def show_splash(self):
        if not os.path.exists(SPLASH_IMAGE):
            self.root.deiconify()
            self.draw()
            return

        self.root.update_idletasks()
        geo = self.root.geometry()
        self.splash = tk.Toplevel(self.root)
        self.splash.overrideredirect(True)
        self.splash.attributes("-topmost", True)
        self.splash.attributes("-alpha", 0.0)
        self.splash.geometry(geo)

        width = self.root.winfo_width()
        height = self.root.winfo_height()
        canvas = tk.Canvas(self.splash, width=width, height=height, highlightthickness=0, bd=0)
        canvas.pack(fill="both", expand=True)
        self.splash_image = tk.PhotoImage(file=SPLASH_IMAGE)
        canvas.create_image(width / 2, height / 2, image=self.splash_image)

        self.play_sound_async(SOUND_HUD_OPENING, duration=self.splash_duration_ms / 1000.0)
        self.fade_splash(0)

    def fade_splash(self, step):
        if not self.splash:
            return
        if step >= self.splash_steps:
            self.end_splash()
            return
        alpha = (step + 1) / self.splash_steps
        self.splash.attributes("-alpha", alpha)
        self.root.after(self.splash_step_ms, lambda: self.fade_splash(step + 1))

    def end_splash(self):
        if self.splash:
            self.splash.destroy()
            self.splash = None
        self.root.deiconify()
        self.draw()

    # =========================
    # Macro recording
    # =========================
    def on_click(self, x, y, button, pressed):
        log_to_file(f"on_click button={button} pressed={pressed} x={x} y={y}")
        global recording
        if recording and pressed:
            click_positions.append((x, y))
            self.log(f"Click: {x}, {y}")

        if button == mouse.Button.x1:
            if pressed:
                self.start_xbutton_autoclicker()
            else:
                self.stop_xbutton_autoclicker()

        if button == mouse.Button.middle and pressed:
            self.start_mbutton_sequence()

        if button != mouse.Button.left:
            return

        if pressed:
            if self.grid_mode == "calibrate" and not self.grid_drag_active:
                if self.grid_rect and self.point_in_rect(x, y, self.grid_rect):
                    self.grid_calibration_points.append((x, y))
                    if len(self.grid_calibration_points) == 1:
                        self.log("Click 2 pentru distanta dintre puncte.")
                    elif len(self.grid_calibration_points) == 2:
                        log_to_file("Calibration click 2 received.")
                        (x1, y1), (x2, y2) = self.grid_calibration_points
                        rx1, ry1, rx2, ry2 = self.normalize_rect(self.grid_rect)
                        self.state["grid_margin_x"] = int(min(x1 - rx1, rx2 - x1))
                        self.state["grid_margin_y"] = int(min(y1 - ry1, ry2 - y1))
                        self.state["grid_spacing_x"] = int(max(1, abs(x2 - x1)))
                        self.state["grid_spacing_y"] = int(max(1, abs(y2 - y1)))
                        self.state["grid_ready"] = True
                        self.grid_mode = None
                        self.grid_rect = None
                        self.grid_calibration_points = []
                        self.persist()
                        self.log("Catapults setat. Poti desena din nou pentru plasare.")
                return

            if self.shift_down and not self.grid_drag_active:
                log_to_file(f"Shift drag start mode grid_ready={self.state['grid_ready']}")
                if self.state["grid_ready"]:
                    self.grid_mode = "place"
                elif self.grid_mode != "calibrate":
                    self.grid_mode = "calibrate"
                    self.log("Deseneaza cadrul pentru calibrare.")
                self.grid_drag_active = True
                self.grid_drag_kind = self.grid_mode
                self.grid_drag_start = (x, y)
                self.grid_rect = (x, y, x, y)
                self.draw()
                return

            if self.rotation_mode and not self.grid_drag_active and self.rotation_rect is None:
                log_to_file("Rotation drag start.")
                self.grid_drag_active = True
                self.grid_drag_kind = "rotate"
                self.grid_drag_start = (x, y)
                self.rotation_rect = (x, y, x, y)
                self.rotation_visible = True
                self.draw()
                return
        else:
            if not self.grid_drag_active:
                return
            self.grid_drag_active = False
            x1, y1 = self.grid_drag_start
            rect = (x1, y1, x, y)
            kind = self.grid_drag_kind
            self.grid_drag_kind = None
            self.grid_drag_start = None

            if kind == "calibrate":
                log_to_file("Calibration drag release.")
                self.grid_rect = rect
                self.grid_calibration_points = []
                self.log("Click 1 pentru margine.")
                self.draw()
                return

            if kind == "place":
                log_to_file("Placement drag release.")
                self.grid_rect = rect
                points = self.compute_grid_points(rect)
                log_to_file(f"Placement points={len(points)}")
                delay_sec = max(0, int(self.state["delay_ms"])) / 1000.0
                for idx, (px, py) in enumerate(points, start=1):
                    mouse_controller.position = (px, py)
                    mouse_controller.click(mouse.Button.left)
                    time.sleep(delay_sec)
                self.grid_rect = None
                self.grid_mode = None
                self.draw()
                return

            if kind == "rotate":
                log_to_file("Rotation rect set.")
                self.rotation_rect = rect
                self.rotation_visible = True
                self.log("Rotire: tine CTRL si foloseste rotita.")
                self.draw()
                return

    def on_move_global(self, x, y):
        if not self.grid_drag_active or not self.grid_drag_start:
            return
        log_to_file(f"Drag move x={x} y={y} kind={self.grid_drag_kind}")
        x1, y1 = self.grid_drag_start
        if self.grid_drag_kind == "rotate":
            self.rotation_rect = (x1, y1, x, y)
        else:
            self.grid_rect = (x1, y1, x, y)
        self.draw()

    def on_scroll_global(self, _x, _y, _dx, dy):
        if not self.rotation_mode or not self.ctrl_down or not self.rotation_rect:
            return
        log_to_file(f"Rotate scroll dy={dy}")
        step = 1.5 if dy > 0 else -1.5
        self.state["grid_angle_deg"] = (self.state["grid_angle_deg"] + step) % 360
        self.last_rotation_time = time.time()
        self.rotation_visible = True
        self.persist()
        self.draw()

    def on_key_release_global(self, key):
        if key in (keyboard.Key.shift, keyboard.Key.shift_l, keyboard.Key.shift_r):
            self.shift_down = False
        if key in (keyboard.Key.ctrl, keyboard.Key.ctrl_l, keyboard.Key.ctrl_r):
            self.ctrl_down = False

    def check_rotation_hide(self):
        if self.rotation_visible and not self.ctrl_down and self.last_rotation_time:
            if time.time() - self.last_rotation_time > 3.0:
                self.rotation_visible = False
                self.draw()
        self.root.after(200, self.check_rotation_hide)

    # =========================
    # Drawing helpers
    # =========================
    def shade(self, hex_color, factor):
        r, g, b = hex_to_rgb(hex_color)
        r = clamp(r * factor, 0, 255)
        g = clamp(g * factor, 0, 255)
        b = clamp(b * factor, 0, 255)
        return rgb_to_hex(r, g, b)

    def rect(self, x1, y1, x2, y2, fill, outline=None, width=1, r=10):
        if r <= 0:
            return self.canvas.create_rectangle(x1, y1, x2, y2, fill=fill, outline=outline, width=width)
        pts = [
            x1+r, y1, x2-r, y1, x2, y1, x2, y1+r,
            x2, y2-r, x2, y2, x2-r, y2, x1+r, y2,
            x1, y2, x1, y2-r, x1, y1+r, x1, y1
        ]
        return self.canvas.create_polygon(pts, fill=fill, outline=outline, width=width, smooth=True)

    def text(self, x, y, s, fill, anchor="w", size=10, weight="normal"):
        return self.canvas.create_text(
            x, y, text=s, fill=fill, anchor=anchor,
            font=("Segoe UI", size, weight)
        )

    def hitbox(self, name, x1, y1, x2, y2, cb=None, kind="button"):
        self.hit[name] = {"x1": x1, "y1": y1, "x2": x2, "y2": y2, "cb": cb, "kind": kind}

    def inside(self, x, y, box):
        return box["x1"] <= x <= box["x2"] and box["y1"] <= y <= box["y2"]

    # =========================
    # Widgets
    # =========================
    def draw_button(self, name, x, y, w, h, label, cb, fill_override=None, accent=False):
        if fill_override is not None:
            base = fill_override
        else:
            base = self.state["accent"] if accent else self.shade(self.state["panel"], 1.12)
        hover = self.shade(base, 1.12)
        fill = hover if self.hover == name else base
        self.rect(x, y, x+w, y+h, fill=fill, outline=self.shade(fill, 0.72), width=1, r=10)
        self.text(x+w/2, y+h/2, label, fill=self.state["text"], anchor="c", size=10,
                  weight="bold" if accent else "normal")
        self.hitbox(name, x, y, x+w, y+h, cb=cb, kind="button")

    def draw_checkbox(self, name, x, y, label, key):
        box = 16
        fill = self.shade(self.state["panel"], 1.10)
        self.rect(x, y, x+box, y+box, fill=fill, outline=self.shade(fill, 0.72), width=1, r=4)

        if self.state[key]:
            self.canvas.create_line(x+4, y+9, x+7, y+12, x+13, y+4, fill=self.state["accent"], width=2)

        self.text(x+box+10, y+box/2, label, fill=self.state["text"], anchor="w", size=10)
        self.hitbox(name, x, y, x+box+10+240, y+box, cb=lambda: self.toggle(key), kind="checkbox")

    def toggle(self, key):
        self.state[key] = not self.state[key]
        self.persist()
        self.draw()

    def draw_slider_ms(self, name, x, y, w, label, key):
        h = 18
        if label:
            self.text(x, y-12, label, fill=self.state["muted"], anchor="w", size=9)

        track = self.shade(self.state["panel"], 1.08)
        self.rect(x, y, x+w, y+h, fill=track, outline=self.shade(track, 0.7), width=1, r=10)

        vmax = self.state["delay_max"]
        value = self.state[key]
        t = (value / vmax) if vmax else 0.0
        t = clamp(t, 0.0, 1.0)
        fw = w * t

        self.rect(x, y, x+fw, y+h, fill=self.state["accent"], outline=None, width=0, r=10)
        kx = x + fw
        self.canvas.create_oval(kx-7, y+h/2-7, kx+7, y+h/2+7,
                                fill=self.shade(self.state["panel"], 1.25),
                                outline=self.shade(self.state["panel"], 0.7), width=1)
        self.text(x+w+10, y+h/2, f'{value} ms', fill=self.state["text"], anchor="w", size=9)
        self.hitbox(name, x, y, x+w, y+h, cb=None, kind="slider_ms")
        self.slider_map[name] = key

    def set_slider_ms_from_x(self, name, mx):
        box = self.hit[name]
        x1, x2 = box["x1"], box["x2"]
        t = clamp((mx - x1) / (x2 - x1), 0.0, 1.0)
        key = self.slider_map.get(name)
        if not key:
            return
        self.state[key] = int(round(t * self.state["delay_max"]))
        if key == "delay_ms":
            self.delay_var.set(str(self.state[key]))
        elif key == "xbutton_delay_ms":
            self.xbutton_delay_var.set(str(self.state[key]))
        elif key == "mbutton_delay_ms":
            self.mbutton_delay_var.set(str(self.state[key]))
        if self.last_slider_values.get(key) != self.state[key]:
            self.last_slider_values[key] = self.state[key]
            label = self.slider_labels.get(key, key)
            self.log(f"{label} setat la {self.state[key]} ms")
        self.persist()

    # =========================
    # Color picker (docked) - NOT persisted
    # =========================
    def open_color_dock(self, key, title, panel_bounds):
        r, g, b = hex_to_rgb(self.state[key])
        h, s, v = colorsys.rgb_to_hsv(r/255, g/255, b/255)
        self.color_dock = {"key": key, "title": title, "h": h, "s": s, "v": v, "drag": None, "panel": panel_bounds}
        self.draw()

    def hsv_to_hex(self, h, s, v):
        r, g, b = colorsys.hsv_to_rgb(h, s, v)
        return rgb_to_hex(r*255, g*255, b*255)

    def close_color_dock(self):
        self.color_dock = None
        self.draw()

    def apply_color(self, hex_color):
        self.state[self.color_dock["key"]] = hex_color
        self.close_color_dock()

    def update_dock_from_mouse(self, kind, mx, my):
        if not self.color_dock:
            return
        if kind == "dock_sv":
            box = self.hit["dock_sv"]
            s = (mx - box["x1"]) / (box["x2"] - box["x1"])
            v = 1.0 - (my - box["y1"]) / (box["y2"] - box["y1"])
            self.color_dock["s"] = clamp(s, 0.0, 1.0)
            self.color_dock["v"] = clamp(v, 0.0, 1.0)
        elif kind == "dock_hue":
            box = self.hit["dock_hue"]
            h = (my - box["y1"]) / (box["y2"] - box["y1"])
            self.color_dock["h"] = clamp(h, 0.0, 1.0)
        self.draw()

    def draw_color_dock(self):
        if not self.color_dock:
            return

        px1, py1, px2, py2 = self.color_dock["panel"]
        dock_w, dock_h = 320, 220

        dx1 = clamp(px2 - dock_w - 14, px1 + 14, px2 - 14 - dock_w)
        dy1 = clamp(py1 + 70, py1 + 14, py2 - 14 - dock_h)
        dx2, dy2 = dx1 + dock_w, dy1 + dock_h

        card = self.shade(self.state["panel"], 1.06)
        self.rect(dx1, dy1, dx2, dy2, fill=card, outline=self.shade(card, 0.72), width=1, r=14)
        self.text(dx1+14, dy1+16, f'{self.color_dock["title"]} Color', fill=self.state["text"],
                  anchor="w", size=11, weight="bold")

        self.draw_button("dock_close", dx2-46, dy1+10, 32, 22, "X", self.close_color_dock, accent=False)

        svx, svy = dx1+14, dy1+44
        svw, svh = 190, 150
        steps = 36
        h = self.color_dock["h"]

        for iy in range(steps):
            vv = 1.0 - (iy/(steps-1))
            for ix in range(steps):
                ss = ix/(steps-1)
                col = self.hsv_to_hex(h, ss, vv)
                x1 = svx + ix*(svw/steps)
                y1 = svy + iy*(svh/steps)
                x2 = svx + (ix+1)*(svw/steps)
                y2 = svy + (iy+1)*(svh/steps)
                self.canvas.create_rectangle(x1, y1, x2, y2, fill=col, outline="")

        self.canvas.create_rectangle(svx, svy, svx+svw, svy+svh,
                                     outline=self.shade(self.state["panel"], 0.65), width=1)

        cx = svx + self.color_dock["s"]*svw
        cy = svy + (1.0-self.color_dock["v"])*svh
        self.canvas.create_oval(cx-6, cy-6, cx+6, cy+6, outline="#ffffff", width=2)

        self.hitbox("dock_sv", svx, svy, svx+svw, svy+svh, cb=None, kind="dock_sv")

        hx = svx+svw+12
        hy = svy
        hw = 22
        hh = svh

        for iy in range(steps*2):
            hhue = iy/(steps*2-1)
            col = self.hsv_to_hex(hhue, 1.0, 1.0)
            y1 = hy + iy*(hh/(steps*2))
            y2 = hy + (iy+1)*(hh/(steps*2))
            self.canvas.create_rectangle(hx, y1, hx+hw, y2, fill=col, outline="")

        self.canvas.create_rectangle(hx, hy, hx+hw, hy+hh,
                                     outline=self.shade(self.state["panel"], 0.65), width=1)

        hm = hy + self.color_dock["h"]*hh
        self.canvas.create_rectangle(hx-2, hm-2, hx+hw+2, hm+2, outline="#ffffff", width=1)

        self.hitbox("dock_hue", hx, hy, hx+hw, hy+hh, cb=None, kind="dock_hue")

        cur_hex = self.hsv_to_hex(self.color_dock["h"], self.color_dock["s"], self.color_dock["v"])
        px = hx+hw+12
        py = svy
        self.text(px, py, "Preview", fill=self.state["muted"], anchor="w", size=9)
        self.rect(px, py+16, px+86, py+56, fill=cur_hex,
                  outline=self.shade(self.state["panel"], 0.65), width=1, r=10)
        self.text(px, py+68, cur_hex.upper(), fill=self.state["text"], anchor="w", size=9)
        self.draw_button("dock_apply", px, py+98, 100, 28, "Apply", lambda: self.apply_color(cur_hex), accent=True)

    # =========================
    # Keybind edit
    # =========================
    def start_edit_bind(self, action_name):
        self.bind_edit = {"action": action_name}
        self.log(f'Edit bind "{action_name}" -> apasă o tastă...')
        self.draw()

    def set_bind_from_key(self, key):
        if not self.bind_edit:
            return
        action = self.bind_edit["action"]
        self.hotkeys[action] = key
        self.bind_edit = None
        self.persist()
        self.log(f'Bind set pentru "{action}"')
        self.draw()

    def key_to_label(self, key):
        try:
            if isinstance(key, keyboard.Key):
                return str(key).replace("Key.", "").upper()
            if isinstance(key, keyboard.KeyCode):
                # FIX: show NUM0, not VK96
                if key.vk == 96:
                    return "NUM0"
                if key.char:
                    return key.char.upper()
                if key.vk:
                    return f"VK{key.vk}"
        except Exception:
            pass
        return "?"

    # =========================
    # Macro actions
    # =========================
    def start_record(self):
        global recording, click_positions
        recording = True
        click_positions = []
        self.log("Înregistrare începută...")

    def stop_record(self):
        global recording
        recording = False
        save_clicks()
        self.log(f"Salvat: {len(click_positions)} click-uri.")

    def play(self):
        if self.play_running:
            self.log("Play deja rulează (nu pornesc încă o dată).")
            return
        self.log("Play pornit.")
        self.play_sound_async(SOUND_STARTED)

        def _run():
            self.play_running = True
            self.play_stop_event.clear()
            try:
                positions = load_clicks()
            except FileNotFoundError:
                self.log("Nu există fișierul de click-uri.")
                return
            except Exception as e:
                self.log(f"Eroare la citire: {e}")
                return

            if not positions:
                self.log("Nu ai click-uri salvate.")
                return

            delay_sec = max(0, int(self.state["delay_ms"])) / 1000.0

            # SINGLE RUN ONLY (no loop)
            if self.state["relative"]:
                start_x, start_y = mouse_controller.position
                base_x, base_y = positions[0]
                for x, y in positions:
                    if self.play_stop_event.is_set():
                        self.log("Play oprit.")
                        return
                    nx = start_x + (x - base_x)
                    ny = start_y + (y - base_y)
                    mouse_controller.position = (nx, ny)
                    mouse_controller.click(mouse.Button.left)
                    if self.play_stop_event.wait(delay_sec):
                        self.log("Play oprit.")
                        return
            else:
                for x, y in positions:
                    if self.play_stop_event.is_set():
                        self.log("Play oprit.")
                        return
                    mouse_controller.position = (x, y)
                    mouse_controller.click(mouse.Button.left)
                    if self.play_stop_event.wait(delay_sec):
                        self.log("Play oprit.")
                        return

            self.log("Play gata.")
        
        def _runner():
            try:
                _run()
            finally:
                self.play_running = False
                self.play_stop_event.clear()

        threading.Thread(target=_runner, daemon=True).start()

    def start_xbutton_autoclicker(self):
        if self.xbutton_thread and self.xbutton_thread.is_alive():
            return

        self.xbutton_stop_event.clear()
        self.log("Autoclicker pornit.")

        def _run():
            while not self.xbutton_stop_event.is_set():
                mouse_controller.click(mouse.Button.left)
                delay_sec = max(0, int(self.state["xbutton_delay_ms"])) / 1000.0
                if self.xbutton_stop_event.wait(delay_sec):
                    break

        self.xbutton_thread = threading.Thread(target=_run, daemon=True)
        self.xbutton_thread.start()

    def stop_xbutton_autoclicker(self):
        self.xbutton_stop_event.set()
        self.log("Autoclicker oprit.")

    def toggle_xbutton_autoclicker(self):
        if self.xbutton_thread and self.xbutton_thread.is_alive():
            self.stop_xbutton_autoclicker()
            return
        self.start_xbutton_autoclicker()

    def start_mbutton_sequence(self):
        if self.mbutton_running.is_set():
            self.log("Grups moving rulează deja.")
            return

        self.mbutton_running.set()
        self.mbutton_stop_event.clear()
        self.log("Grups moving pornit (MButton).")

        def _run():
            try:
                delay_sec = max(0, int(self.state["mbutton_delay_ms"])) / 1000.0
                for key_char in ("1", "2", "3", "4"):
                    if self.mbutton_stop_event.is_set():
                        return
                    self.keyboard_controller.press(key_char)
                    self.keyboard_controller.release(key_char)
                    cx, cy = mouse_controller.position
                    click_at(cx, cy)
                    if self.mbutton_stop_event.wait(delay_sec):
                        return
            finally:
                self.mbutton_running.clear()
                self.mbutton_stop_event.clear()
                self.log("Grups moving gata.")

        threading.Thread(target=_run, daemon=True).start()

    def start_catapults_setup(self):
        self.state["grid_ready"] = False
        self.grid_mode = "calibrate"
        self.grid_rect = None
        self.grid_calibration_points = []
        self.log("Catapults: deseneaza cadrul cu SHIFT + drag.")
        self.persist()
        self.draw()

    def toggle_rotation_mode(self):
        self.rotation_mode = not self.rotation_mode
        if self.rotation_mode:
            self.rotation_rect = None
            self.rotation_visible = False
            self.log("Rotire activata: deseneaza cadrul, apoi CTRL + rotita.")
        else:
            self.rotation_visible = False
            self.log("Rotire dezactivata.")
        self.draw()

    def soft_restart(self):
        global recording
        self.play_stop_event.set()
        self.xbutton_stop_event.set()
        self.mbutton_stop_event.set()
        recording = False
        self.play_running = False
        self.mbutton_running.clear()
        self.bind_edit = None
        self.active_slider = None
        self.logs = []
        self.log_scroll_offset = 0
        self.draw()

    def toggle_topmost(self):
        self.topmost = not self.topmost
        self.root.attributes("-topmost", self.topmost)
        self.log(f"Topmost: {'ON' if self.topmost else 'OFF'}")

    def toggle_visible(self):
        # Hide/show without killing the macro
        if self.root.state() == "withdrawn":
            self.show_menu()
        else:
            self.hide_menu()

    def hide_menu(self):
        # save positions + state
        self.persist()
        self.root.withdraw()
        self.eye.deiconify()
        self.eye.attributes("-topmost", True)

    def show_menu(self):
        self.eye.withdraw()
        self.root.deiconify()
        self.root.attributes("-topmost", self.topmost)
        self.persist()

    def quit(self):
        self.persist()
        self.play_stop_event.set()
        self.xbutton_stop_event.set()
        self.mbutton_stop_event.set()
        try:
            self.mouse_listener.stop()
        except Exception:
            pass
        try:
            self.k_listener.stop()
        except Exception:
            pass
        try:
            self.eye.destroy()
        except Exception:
            pass
        try:
            self.root.destroy()
        except Exception:
            pass

    # =========================
    # Delay entry
    # =========================
    def apply_delay_entry(self, _event=None):
        try:
            v = int(self.delay_var.get().strip())
            v = clamp(v, 0, self.state["delay_max"])
            self.state["delay_ms"] = v
            self.delay_var.set(str(v))
            self.persist()
            self.log(f"Delay setat la {v} ms")
            self.draw()
        except Exception:
            self.delay_var.set(str(self.state["delay_ms"]))

    def apply_xbutton_delay_entry(self, _event=None):
        try:
            v = int(self.xbutton_delay_var.get().strip())
            v = clamp(v, 0, self.state["delay_max"])
            self.state["xbutton_delay_ms"] = v
            self.xbutton_delay_var.set(str(v))
            self.persist()
            self.log(f"Autoclicker delay setat la {v} ms")
            self.draw()
        except Exception:
            self.xbutton_delay_var.set(str(self.state["xbutton_delay_ms"]))

    def apply_mbutton_delay_entry(self, _event=None):
        try:
            v = int(self.mbutton_delay_var.get().strip())
            v = clamp(v, 0, self.state["delay_max"])
            self.state["mbutton_delay_ms"] = v
            self.mbutton_delay_var.set(str(v))
            self.persist()
            self.log(f"Grups moving delay setat la {v} ms")
            self.draw()
        except Exception:
            self.mbutton_delay_var.set(str(self.state["mbutton_delay_ms"]))

    # =========================
    # Global key handler
    # =========================
    def on_key_press_global(self, key):
        if key in (keyboard.Key.shift, keyboard.Key.shift_l, keyboard.Key.shift_r):
            self.shift_down = True
        if key in (keyboard.Key.ctrl, keyboard.Key.ctrl_l, keyboard.Key.ctrl_r):
            self.ctrl_down = True
        # edit bind capture
        if self.bind_edit is not None:
            if key == keyboard.Key.esc:
                self.bind_edit = None
                self.log("Edit bind anulată.")
                self.draw()
                return
            self.set_bind_from_key(key)
            return
        if key == keyboard.Key.esc and (self.grid_mode or self.rotation_mode):
            self.grid_mode = None
            self.grid_rect = None
            self.grid_drag_active = False
            self.grid_calibration_points = []
            self.rotation_mode = False
            self.rotation_visible = False
            self.log("Anulat.")
            self.draw()
            return

        try:
            if key == self.hotkeys["start"]:
                self.start_record()
            elif key == self.hotkeys["stop"]:
                self.stop_record()
            elif key == self.hotkeys["play"]:
                self.play()
            elif key == self.hotkeys["xbutton_toggle"]:
                self.toggle_xbutton_autoclicker()
            elif key == self.hotkeys["mbutton_sequence"]:
                self.start_mbutton_sequence()
            elif key == self.hotkeys["quit"]:
                self.quit()
            elif key == self.hotkeys["toggle_topmost"]:
                self.toggle_topmost()
            elif key == self.hotkeys["toggle_visible"]:
                self.toggle_visible()
        except Exception:
            pass

    # =========================
    # Eye drag
    # =========================
    def on_eye_down(self, e):
        self.eye_drag["active"] = True
        self.eye_drag["x"] = e.x
        self.eye_drag["y"] = e.y

    def on_eye_up(self, e):
        # click on eye = show menu
        self.eye_drag["active"] = False
        self.persist()
        # If it was a simple click (tiny movement), show menu
        # (Tk doesn't give delta clean here; keep it simple: always show on release)
        self.show_menu()

    def on_eye_drag(self, e):
        if not self.eye_drag["active"]:
            return
        x = self.eye.winfo_x() + (e.x - self.eye_drag["x"])
        y = self.eye.winfo_y() + (e.y - self.eye_drag["y"])
        self.eye.geometry(f"+{x}+{y}")

    # =========================
    # Mouse events
    # =========================
    def find_hover(self, x, y):
        for k, box in self.hit.items():
            if self.inside(x, y, box):
                return k
        return None

    def on_motion(self, e):
        self.hover = self.find_hover(e.x, e.y)
        self.draw()

    def on_down(self, e):
        # title drag
        if e.y <= self.title_h and self.hover not in ("win_close", "win_hide", "win_restart"):
            self.drag["active"] = True
            self.drag["x"] = e.x
            self.drag["y"] = e.y
            return

        # bind overlay ignores clicks
        if self.bind_edit is not None:
            return

        hid = self.find_hover(e.x, e.y)
        if not hid:
            return
        box = self.hit[hid]
        kind = box["kind"]

        if kind in ("dock_sv", "dock_hue"):
            if kind == "dock_sv":
                self.color_dock["drag"] = "sv"
                self.update_dock_from_mouse("dock_sv", e.x, e.y)
            else:
                self.color_dock["drag"] = "hue"
                self.update_dock_from_mouse("dock_hue", e.x, e.y)
            return

        if kind in ("button", "checkbox", "tab", "link"):
            if box["cb"]:
                if hid in self.button_labels:
                    self.log(f'Buton apăsat: {self.button_labels[hid]}')
                box["cb"]()
            return

        if kind == "slider_ms":
            self.active_slider = hid
            self.set_slider_ms_from_x(hid, e.x)
            self.draw()
            return

        if kind == "log_scroll":
            self.active_slider = hid
            self.set_log_scroll_from_y(e.y)
            self.draw()

    def on_up(self, e):
        if self.drag["active"]:
            self.persist()
        self.drag["active"] = False
        self.active_slider = None
        if self.color_dock:
            self.color_dock["drag"] = None

    def on_drag(self, e):
        if self.drag["active"]:
            x = self.root.winfo_x() + (e.x - self.drag["x"])
            y = self.root.winfo_y() + (e.y - self.drag["y"])
            self.root.geometry(f"+{x}+{y}")
            return

        if self.active_slider:
            if self.hit[self.active_slider]["kind"] == "slider_ms":
                self.set_slider_ms_from_x(self.active_slider, e.x)
            elif self.hit[self.active_slider]["kind"] == "log_scroll":
                self.set_log_scroll_from_y(e.y)
            self.draw()
            return

        if self.color_dock and self.color_dock.get("drag"):
            if self.color_dock["drag"] == "sv":
                self.update_dock_from_mouse("dock_sv", e.x, e.y)
            else:
                self.update_dock_from_mouse("dock_hue", e.x, e.y)

    # =========================
    # Tabs
    # =========================
    def set_tab(self, name):
        self.active_tab = name
        self.persist()
        self.draw()

    def draw_tabs(self, x, y, w):
        # three tabs, same style
        tabs = ["Commands", "Theme", "About me"]
        tw = int((w - 20) / 3)
        th = 26
        gap = 8
        cx = x
        for t in tabs:
            active = (t == self.active_tab)
            base = self.state["accent"] if active else self.shade(self.state["panel"], 1.06)
            self.rect(cx, y, cx+tw, y+th, fill=base, outline=self.shade(base, 0.72), width=1, r=10)
            self.text(cx+tw/2, y+th/2, t, fill=self.state["text"], anchor="c", size=9,
                      weight="bold" if active else "normal")
            self.hitbox(f"tab_{t}", cx, y, cx+tw, y+th, cb=lambda tt=t: self.set_tab(tt), kind="tab")
            cx += tw + gap

    # =========================
    # Main draw
    # =========================
    def draw(self):
        self.canvas.delete("all")
        self.hit = {}
        self.slider_map = {}
        self.button_labels = {}

        W = self.canvas.winfo_width()
        H = self.canvas.winfo_height()

        # Background + title
        self.canvas.create_rectangle(0, 0, W, H, fill=self.state["bg"], outline="")
        self.canvas.create_rectangle(0, 0, W, self.title_h, fill=self.state["title"], outline="")
        self.text(self.pad, self.title_h/2, "IM QUATRO MACROS", fill=self.state["text"], anchor="w", size=10, weight="bold")

        # Restart button
        self.draw_button("win_restart", W-140, 6, 36, self.title_h-12, "⟳", self.soft_restart, accent=False)

        # Hide button (👁) in titlebar
        self.draw_button("win_hide", W-96, 6, 40, self.title_h-12, "👁", self.hide_menu, accent=False)

        # Close button
        self.draw_button("win_close", W-52, 6, 44, self.title_h-12, "✕", self.quit, accent=False)

        # Main panel
        x = self.pad
        y = self.title_h + self.pad
        w = W - self.pad*2
        h = H - y - self.pad

        self.rect(x, y, x+w, y+h, fill=self.state["panel"], outline=self.shade(self.state["panel"], 0.72), width=1, r=14)

        # Tabs
        self.draw_tabs(x+14, y+14, w-28)

        # Content area
        content_x = x + 14
        content_y = y + 14 + 34
        content_w = w - 28
        content_h = h - (14 + 34 + 14)

        # Per tab draw
        if self.active_tab == "Commands":
            self.draw_tab_commands(content_x, content_y, content_w, content_h)
        elif self.active_tab == "Theme":
            self.draw_tab_theme(content_x, content_y, content_w, content_h, (x, y, x+w, y+h))
        else:
            self.draw_tab_about(content_x, content_y, content_w, content_h)

        # Docked color picker (theme tab only, but ok to keep if open)
        self.draw_color_dock()

        # Bind overlay
        if self.bind_edit is not None:
            self.draw_bind_overlay()

        # Grid overlay
        self.draw_grid_overlay()

    def draw_grid_overlay(self):
        rect = None
        if self.grid_drag_active and self.grid_rect:
            rect = self.grid_rect
        elif self.grid_mode == "calibrate" and self.grid_rect:
            rect = self.grid_rect
        elif self.grid_mode == "place" and self.grid_rect:
            rect = self.grid_rect
        if self.rotation_visible and self.rotation_rect:
            rect = self.rotation_rect

        if not rect:
            return

        x1, y1, x2, y2 = self.normalize_rect(rect)
        c1x, c1y = self.screen_to_canvas(x1, y1)
        c2x, c2y = self.screen_to_canvas(x2, y2)

        outline = self.state["accent"] if self.state["grid_ready"] else "#d63a3a"
        self.canvas.create_rectangle(c1x, c1y, c2x, c2y, outline=outline, width=2)

        if self.state["grid_ready"]:
            points = self.compute_grid_points(rect)
            for idx, (px, py) in enumerate(points, start=1):
                cx, cy = self.screen_to_canvas(px, py)
                r = 4
                self.canvas.create_oval(cx - r, cy - r, cx + r, cy + r, fill=outline, outline="")
                self.text(cx + 6, cy - 6, str(idx), fill=self.state["text"], anchor="w", size=8)

    def draw_tab_commands(self, x, y, w, h):
        # Left area: commands + hotkeys
        lx = x
        ly = y
        log_w = 260
        gap = 16
        left_w = w - log_w - gap

        # Fixed button colors
        BTN_START = "#2d78ff"   # blue
        BTN_STOP = "#d63a3a"   # red
        BTN_PLAY = "#2fd06f"   # green
        BTN_QUIT = "#7a1f1f"   # dark red

        self.text(lx, ly, "Commands", fill=self.state["text"], anchor="w", size=10, weight="bold")
        ly += 22

        cat_color = "#2fd06f" if self.state["grid_ready"] else "#d63a3a"
        self.draw_button("cmd_catapults", lx, ly, 170, 28, "Catapults", self.start_catapults_setup,
                         fill_override=cat_color, accent=False)
        self.text(lx+180, ly+14, "[SET]" if self.state["grid_ready"] else "[NESETAT]",
                  fill=self.state["muted"], anchor="w", size=9)
        self.draw_button("edit_catapults", lx+240, ly, 60, 28, "Edit",
                         self.start_catapults_setup, accent=False)
        ly += 36

        rotate_color = "#2fd06f" if self.rotation_mode else self.shade(self.state["panel"], 1.12)
        self.draw_button("cmd_rotate", lx, ly, 170, 28, "Rotate Frame", self.toggle_rotation_mode,
                         fill_override=rotate_color, accent=False)
        self.text(lx+180, ly+14, "[CTRL+WHEEL]", fill=self.state["muted"], anchor="w", size=9)
        self.draw_button("edit_rotate", lx+240, ly, 60, 28, "Toggle",
                         self.toggle_rotation_mode, accent=False)
        ly += 36

        ly = self.draw_command_row(lx, ly, left_w, "Start Record", "start", self.start_record,
                                   description="Record", fill_override=BTN_START)
        ly = self.draw_command_row(lx, ly, left_w, "Stop Record", "stop", self.stop_record,
                                   description="Stop", fill_override=BTN_STOP)
        ly = self.draw_command_row(lx, ly, left_w, "Play", "play", self.play,
                                   description="Playback", fill_override=BTN_PLAY,
                                   slider_key="delay_ms")
        ly = self.draw_command_row(lx, ly, left_w, "Autoclicker", "xbutton_toggle", self.toggle_xbutton_autoclicker,
                                   description="XButton1 (hold)", slider_key="xbutton_delay_ms")
        ly = self.draw_command_row(lx, ly, left_w, "Grups moving", "mbutton_sequence", self.start_mbutton_sequence,
                                   description="mbuton", slider_key="mbutton_delay_ms")
        ly = self.draw_command_row(lx, ly, left_w, "Quit", "quit", self.quit,
                                   description="Exit", fill_override=BTN_QUIT)

        ly += 10
        self.draw_checkbox("chk_relative", lx, ly, "Relative mode (play from mouse)", "relative")
        ly += 44

        # Hints
        self.text(lx, y+h-16, "F10: Topmost  |  F11: Hide/Show", fill=self.state["muted"], anchor="w", size=9)

        # Right side logs
        rx = x + left_w + gap
        box_h = h - 40
        self.draw_log_console(rx, y, log_w, box_h)

    def draw_tab_theme(self, x, y, w, h, panel_bounds):
        self.text(x, y, "Theme", fill=self.state["text"], anchor="w", size=10, weight="bold")
        y += 18

        y = self.draw_color_row(x, y, "Background", "bg", panel_bounds)
        y = self.draw_color_row(x, y, "Title bar", "title", panel_bounds)
        y = self.draw_color_row(x, y, "Panel", "panel", panel_bounds)
        y = self.draw_color_row(x, y, "Accent", "accent", panel_bounds)
        y = self.draw_color_row(x, y, "Text", "text", panel_bounds)
        y = self.draw_color_row(x, y, "Muted", "muted", panel_bounds)

        self.text(x, y+10, "Note: theme colors are NOT saved on restart (default on relaunch).",
                  fill=self.state["muted"], anchor="w", size=9)

        self.delay_entry.place_forget()
        self.xbutton_delay_entry.place_forget()
        self.mbutton_delay_entry.place_forget()

    def draw_tab_about(self, x, y, w, h):
        self.text(x, y, "About me", fill=self.state["text"], anchor="w", size=10, weight="bold")
        y += 26

        self.text(x, y, "IM QUATRO MACROS - macro click tool.", fill=self.state["text"], anchor="w", size=10)
        y += 22
        self.text(x, y, "Steam:", fill=self.state["muted"], anchor="w", size=9)
        y += 18

        # clickable link
        link_w = 360
        link_h = 26
        self.rect(x, y, x+link_w, y+link_h, fill=self.shade(self.state["panel"], 1.10),
                  outline=self.shade(self.state["panel"], 0.72), width=1, r=10)
        self.text(x+12, y+link_h/2, STEAM_URL, fill=self.state["accent"], anchor="w", size=9, weight="bold")
        self.hitbox("about_link", x, y, x+link_w, y+link_h, cb=lambda: webbrowser.open(STEAM_URL), kind="link")

        y += 44
        self.text(x, y, "Script: records mouse clicks and replays them with delay.", fill=self.state["text"], anchor="w", size=10)
        y += 18
        self.text(x, y, "Use responsibly.", fill=self.state["muted"], anchor="w", size=9)

        self.delay_entry.place_forget()
        self.xbutton_delay_entry.place_forget()
        self.mbutton_delay_entry.place_forget()

    def draw_command_row(self, x, y, row_w, label, action_key, cb, description=None,
                         fill_override=None, slider_key=None):
        btn_w = 150
        btn_h = 28
        key_label = self.key_to_label(self.hotkeys[action_key])
        key_x = x + btn_w + 8
        edit_w = 56
        edit_x = key_x + 62
        desc_x = edit_x + edit_w + 8
        slider_w = 210
        slider_x = x + row_w - slider_w
        text_max_x = slider_x - 10

        self.button_labels[f"cmd_{action_key}"] = label
        key_label = self.key_to_label(self.hotkeys[action_key])
        self.draw_button(f"cmd_{action_key}", x, y, btn_w, btn_h, f"{label}", cb,
                         fill_override=fill_override, accent=False)
        self.text(key_x, y+14, f"[{key_label}]", fill=self.state["muted"], anchor="w", size=9)
        self.draw_button(f"edit_{action_key}", edit_x, y, edit_w, btn_h, "Edit",
                         lambda ak=action_key: self.start_edit_bind(ak), accent=False)
        if description and desc_x < text_max_x:
            self.text(desc_x, y+14, description, fill=self.state["muted"], anchor="w", size=9)
        if slider_key:
            slider_name = f"slider_{slider_key}"
            slider_y = y + 6
            self.draw_slider_ms(slider_name, slider_x, slider_y, slider_w, None, slider_key)
            entry_x = slider_x + slider_w + 6
            entry_y = y + 4
            if slider_key == "delay_ms":
                self.delay_entry.place(x=entry_x, y=entry_y, width=52, height=22)
            elif slider_key == "xbutton_delay_ms":
                self.xbutton_delay_entry.place(x=entry_x, y=entry_y, width=52, height=22)
            elif slider_key == "mbutton_delay_ms":
                self.mbutton_delay_entry.place(x=entry_x, y=entry_y, width=52, height=22)
            self.text(entry_x + 56, entry_y+11, "ms", fill=self.state["muted"], anchor="w", size=9)
        return y + 36

    def draw_log_console(self, x, y, w, h):
        self.text(x, y, "Console", fill=self.state["muted"], anchor="w", size=9)
        box_y = y + 14
        box_h = h
        self.rect(x, box_y, x+w, box_y+box_h,
                  fill=self.shade(self.state["panel"], 1.08),
                  outline=self.shade(self.state["panel"], 0.72), width=1, r=12)

        line_h = 18
        padding = 8
        visible = max(1, int((box_h - padding * 2) / line_h))
        total = len(self.logs)
        max_offset = max(0, total - visible)
        self.log_scroll_offset = clamp(self.log_scroll_offset, 0, max_offset)
        start = max(0, total - visible - self.log_scroll_offset)
        end = start + visible
        lines = self.logs[start:end]

        ly = box_y + padding
        for line in lines:
            self.text(x + 10, ly, line, fill=self.state["text"], anchor="w", size=9)
            ly += line_h

        if total > visible:
            track_x = x + w - 12
            track_y = box_y + padding
            track_h = box_h - padding * 2
            self.rect(track_x, track_y, track_x + 8, track_y + track_h,
                      fill=self.shade(self.state["panel"], 1.2),
                      outline=self.shade(self.state["panel"], 0.7), width=1, r=6)
            knob_h = max(20, int(track_h * (visible / total)))
            max_move = max(1, track_h - knob_h)
            knob_y = track_y + int((self.log_scroll_offset / max_offset) * max_move)
            self.rect(track_x, knob_y, track_x + 8, knob_y + knob_h,
                      fill=self.state["accent"], outline=None, width=0, r=6)
            self.hitbox("log_scroll", track_x - 2, track_y, track_x + 10, track_y + track_h,
                        cb=None, kind="log_scroll")

    def set_log_scroll_from_y(self, my):
        box = self.hit.get("log_scroll")
        if not box:
            return
        track_y1 = box["y1"]
        track_y2 = box["y2"]
        track_h = track_y2 - track_y1
        line_h = 18
        padding = 8
        box_h = track_h + padding * 2
        visible = max(1, int((box_h - padding * 2) / line_h))
        total = len(self.logs)
        max_offset = max(0, total - visible)
        if max_offset == 0:
            self.log_scroll_offset = 0
            return
        knob_h = max(20, int(track_h * (visible / total)))
        max_move = max(1, track_h - knob_h)
        t = clamp((my - track_y1 - knob_h / 2) / max_move, 0.0, 1.0)
        self.log_scroll_offset = int(round(t * max_offset))

    def draw_color_row(self, x, y, label, key, panel_bounds):
        self.text(x, y+12, label, fill=self.state["text"], anchor="w", size=10)
        sx1 = x + 160
        sy1 = y + 2
        sx2 = sx1 + 44
        sy2 = sy1 + 22
        self.rect(sx1, sy1, sx2, sy2, fill=self.state[key], outline=self.shade(self.state["panel"], 0.72), width=1, r=8)
        self.draw_button(f"pick_{key}", sx2+10, y, 70, 26, "Pick",
                         lambda k=key, t=label, pb=panel_bounds: self.open_color_dock(k, t, pb),
                         accent=False)
        return y + 34

    def draw_bind_overlay(self):
        W = self.canvas.winfo_width()
        H = self.canvas.winfo_height()
        self.canvas.create_rectangle(0, 0, W, H, fill="#000000", stipple="gray25", outline="")
        self.rect(W/2-180, H/2-60, W/2+180, H/2+60, fill=self.shade(self.state["panel"], 1.10),
                  outline=self.shade(self.state["panel"], 0.72), width=1, r=14)
        self.text(W/2, H/2-10, "Apasă tasta pentru bind...", fill=self.state["text"], anchor="c", size=12, weight="bold")
        self.text(W/2, H/2+18, "(ESC ca să anulezi)", fill=self.state["muted"], anchor="c", size=10)


# =========================
# Run
# =========================
if __name__ == "__main__":
    try:
        log_to_file("Launching ImGuiApp.")
        app = ImGuiApp()
    except Exception as exc:
        log_to_file(f"FATAL: {exc}")
        log_to_file(traceback.format_exc())
