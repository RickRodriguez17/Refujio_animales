"""Configuración global del sistema (cargada desde variables de entorno)."""

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    secret_key: str = "cambia-esta-clave-en-produccion-refugio-4-patas"
    algorithm: str = "HS256"
    access_token_expire_minutes: int = 480
    database_url: str = "sqlite:///./refugio.db"

    model_config = SettingsConfigDict(env_file=".env", extra="ignore")


settings = Settings()
