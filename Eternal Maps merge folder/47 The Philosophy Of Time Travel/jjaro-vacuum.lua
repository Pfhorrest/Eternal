Triggers = {}

function Triggers.idle()
  for p in Players() do
    if p.polygon.media then
      p.oxygen = p.oxygen - 7
    end
  end
end