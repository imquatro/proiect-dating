import time
from pynput import keyboard, mouse


def run_sequence(delay_ms=160, click_delay_ms=80, post_click_ms=80):
    kb = keyboard.Controller()
    ms = mouse.Controller()
    delay_sec = max(0, int(delay_ms)) / 1000.0
    click_delay_sec = max(0, int(click_delay_ms)) / 1000.0
    post_click_sec = max(0, int(post_click_ms)) / 1000.0

    for key_char in ("1", "2", "3", "4"):
        kb.press(key_char)
        kb.release(key_char)
        time.sleep(click_delay_sec)
        ms.click(mouse.Button.left)
        time.sleep(post_click_sec)
        time.sleep(delay_sec)


def main():
    print("Test10: secventa 1-4 + click (pynput). Apasa pe rotita pentru start.")
    running = {"value": False}

    def on_click(_x, _y, button, pressed):
        if pressed and button == mouse.Button.middle and not running["value"]:
            running["value"] = True
            run_sequence()
            print("Gata. Apasa din nou pe rotita pentru reluare.")
            running["value"] = False
        return True

    with mouse.Listener(on_click=on_click) as listener:
        listener.join()


if __name__ == "__main__":
    main()
