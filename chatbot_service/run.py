"""Start the chatbot with the service's virtual environment when available."""

import subprocess
import sys
from pathlib import Path


SERVICE_DIR = Path(__file__).resolve().parent
ROOT_DIR = SERVICE_DIR.parent
VENV_PYTHON = SERVICE_DIR / "venv" / ("Scripts/python.exe" if sys.platform == "win32" else "bin/python")
PYTHON = VENV_PYTHON if VENV_PYTHON.is_file() else Path(sys.executable)

try:
    raise SystemExit(subprocess.call(
        [str(PYTHON), "-m", "uvicorn", "chatbot_service.app:app", "--host", "127.0.0.1", "--port", "8002"],
        cwd=ROOT_DIR,
    ))
except KeyboardInterrupt:
    raise SystemExit(130)
