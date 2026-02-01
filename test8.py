import time
from pynput import keyboard, mouse


def run_sequence(delay_ms=120):
    controller = mouse.Controller()
    kb = keyboard.Controller()
    delay_sec = max(0, int(delay_ms)) / 1000.0

    for key_char in ("1", "2", "3", "4"):
        kb.press(key_char)
        kb.release(key_char)
        controller.click(mouse.Button.left)
        time.sleep(delay_sec)


def main():
    print("Test8: secventa 1-4 cu click. Apasa Enter pentru start.")
    input()
    run_sequence()
    print("Gata.")


if __name__ == "__main__":
    main()
