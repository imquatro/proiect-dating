import time
import ctypes
from ctypes import wintypes


user32 = ctypes.WinDLL("user32", use_last_error=True)

INPUT_MOUSE = 0
INPUT_KEYBOARD = 1

MOUSEEVENTF_LEFTDOWN = 0x0002
MOUSEEVENTF_LEFTUP = 0x0004

KEYEVENTF_SCANCODE = 0x0008
KEYEVENTF_KEYUP = 0x0002

SCAN_1 = 0x02
SCAN_2 = 0x03
SCAN_3 = 0x04
SCAN_4 = 0x05


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


def key_press_scancode(scan_code):
    _send_input(
        INPUT(type=INPUT_KEYBOARD, union=_INPUTUNION(ki=KEYBDINPUT(0, scan_code, KEYEVENTF_SCANCODE, 0, 0))),
        INPUT(type=INPUT_KEYBOARD, union=_INPUTUNION(ki=KEYBDINPUT(0, scan_code, KEYEVENTF_SCANCODE | KEYEVENTF_KEYUP, 0, 0))),
    )


def left_click_native():
    _send_input(
        INPUT(type=INPUT_MOUSE, union=_INPUTUNION(mi=MOUSEINPUT(0, 0, 0, MOUSEEVENTF_LEFTDOWN, 0, 0))),
        INPUT(type=INPUT_MOUSE, union=_INPUTUNION(mi=MOUSEINPUT(0, 0, 0, MOUSEEVENTF_LEFTUP, 0, 0))),
    )


def run_sequence(delay_ms=120):
    delay_sec = max(0, int(delay_ms)) / 1000.0
    for scan_code in (SCAN_1, SCAN_2, SCAN_3, SCAN_4):
        key_press_scancode(scan_code)
        left_click_native()
        time.sleep(delay_sec)


def main():
    print("Test9: secventa 1-4 (scancode) + click. Apasa Enter pentru start.")
    input()
    run_sequence()
    print("Gata.")


if __name__ == "__main__":
    main()
